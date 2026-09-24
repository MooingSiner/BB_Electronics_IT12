<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($request->filled('search'), fn ($query) => $query->where(
                'product_name', 'like', '%'.$request->string('search').'%'
            ))
            ->when($request->filled('category'), fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('category_name', 'like', '%'.$request->string('category').'%')
            ))
            ->orderBy('product_name')
            ->get()
            ->map(fn (Product $product) => (object) [
                'id' => $product->product_id,
                'name' => $product->product_name,
                'price' => (float) $product->unit_price,
                'stock' => $product->quantity_on_hand,
                'reorder_level' => $product->reorder_level,
            ]);

        $cart = session('cart', []);
        $subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);

        $discountType = session('discount_type', 'none');
        $discountValue = (float) session('discount_value', 0);
        $discountAmount = $this->calculateDiscount($subtotal, $discountType, $discountValue);

        $total = max(0, $subtotal - $discountAmount);
        $vat = $total - ($total / 1.12);

        $amountReceived = (float) session('amount_received', 0);
        $change = session('payment', 'Cash') === 'Cash' && $amountReceived > 0
            ? $amountReceived - $total
            : null;

        return view('cashier.pos', compact('products', 'subtotal', 'discountAmount', 'total', 'vat', 'change'));
    }

    public function add(Request $request): RedirectResponse
    {
        $request->validate(['product_id' => ['required', 'integer']]);

        $product = Product::findOrFail($request->integer('product_id'));
        $cart = session('cart', []);
        $key = (string) $product->product_id;
        $currentQty = $cart[$key]['quantity'] ?? 0;

        if ($currentQty + 1 > $product->quantity_on_hand) {
            return back()->with('error', "Only {$product->quantity_on_hand} unit(s) of {$product->product_name} available.");
        }

        $cart[$key] = [
            'product_id' => $product->product_id,
            'name' => $product->product_name,
            'price' => (float) $product->unit_price,
            'quantity' => $currentQty + 1,
        ];

        session(['cart' => $cart]);

        return back();
    }

    public function remove(Request $request): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$request->string('cart_key')->toString()]);
        session(['cart' => $cart]);

        return back();
    }

    public function updateQuantity(Request $request): RedirectResponse
    {
        $key = $request->string('cart_key')->toString();
        $cart = session('cart', []);

        if (! isset($cart[$key])) {
            return back();
        }

        $product = Product::find($cart[$key]['product_id']);
        $quantity = max(1, $request->integer('quantity'));

        if ($product && $quantity > $product->quantity_on_hand) {
            $quantity = $product->quantity_on_hand;
        }

        $cart[$key]['quantity'] = $quantity;
        session(['cart' => $cart]);

        return back();
    }

    public function clear(): RedirectResponse
    {
        session()->forget(['cart', 'discount_type', 'discount_value', 'payment', 'amount_received']);

        return back();
    }

    public function discount(Request $request): RedirectResponse
    {
        if ($request->has('discount_type')) {
            $type = $request->string('discount_type')->toString();
            session(['discount_type' => $type]);

            if ($type === 'none') {
                session()->forget('discount_value');
            }
        }

        if ($request->boolean('apply_discount')) {
            session(['discount_value' => $request->input('discount_value', 0)]);
        }

        return back();
    }

    public function payment(Request $request): RedirectResponse
    {
        session(['payment' => $request->string('payment')->toString()]);

        return back();
    }

    public function amount(Request $request): RedirectResponse
    {
        session(['amount_received' => $request->input('amount_received', 0)]);

        return back();
    }

    public function complete(Request $request): RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Add at least one product before completing the sale.');
        }

        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);

            if (! $product || $product->quantity_on_hand < $item['quantity']) {
                return back()->with('error', "Not enough stock for {$item['name']}.");
            }
        }

        $subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $discountType = session('discount_type', 'none');
        $discountValue = (float) session('discount_value', 0);
        $discountAmount = $this->calculateDiscount($subtotal, $discountType, $discountValue);
        $total = max(0, $subtotal - $discountAmount);

        $paymentLabel = session('payment', 'Cash');
        $paymentMethod = match ($paymentLabel) {
            'GCash' => PaymentMethod::GCash,
            'Cheque' => PaymentMethod::Cheque,
            default => PaymentMethod::Cash,
        };

        if ($paymentMethod === PaymentMethod::Cash) {
            $amountReceived = (float) session('amount_received', 0);

            if ($amountReceived < $total) {
                return back()->with('error', 'Amount received is less than the total due.');
            }

            $changeAmount = $amountReceived - $total;
        } else {
            $amountReceived = $total;
            $changeAmount = 0;
        }

        $sale = DB::transaction(function () use ($cart, $subtotal, $discountAmount, $total, $paymentMethod, $amountReceived, $changeAmount) {
            $sale = Sale::create([
                'user_id' => Auth::id(),
                'sale_date' => now(),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $total,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountReceived,
                'change_amount' => $changeAmount,
                'status' => SaleStatus::Completed,
            ]);

            foreach ($cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->sale_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            return $sale;
        });

        session()->forget(['cart', 'discount_type', 'discount_value', 'payment', 'amount_received']);

        return redirect()->route('cashier.pos')->with([
            'sale_complete' => true,
            'sale_txn_id' => $sale->sale_id,
            'sale_subtotal' => number_format($subtotal, 2),
            'sale_discount' => number_format($discountAmount, 2),
            'sale_total' => number_format($total, 2),
            'sale_payment' => $paymentLabel,
            'sale_received' => number_format($amountReceived, 2),
            'sale_change' => number_format($changeAmount, 2),
        ]);
    }

    private function calculateDiscount(float $subtotal, string $type, float $value): float
    {
        return match ($type) {
            'percent' => round($subtotal * min(max($value, 0), 100) / 100, 2),
            'fixed' => min(max($value, 0), $subtotal),
            default => 0,
        };
    }
}
