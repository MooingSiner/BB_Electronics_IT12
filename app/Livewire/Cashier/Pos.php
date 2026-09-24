<?php

namespace App\Livewire\Cashier;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::cashier-pos')]
#[Title('Point of Sale')]
class Pos extends Component
{
    public string $search = '';

    public string $category = '';

    /** @var array<string, array{product_id: int, name: string, price: float, quantity: int}> */
    public array $cart = [];

    public string $discountType = 'none';

    public float $discountValue = 0;

    public string $payment = 'Cash';

    public float $amountReceived = 0;

    public ?string $errorMessage = null;

    /** @var array<string, string>|null */
    public ?array $completedSale = null;

    public function mount(): void
    {
        $this->cart = session('cart', []);
        $this->discountType = session('discount_type', 'none');
        $this->discountValue = (float) session('discount_value', 0);
        $this->payment = session('payment', 'Cash');
        $this->amountReceived = (float) session('amount_received', 0);
    }

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);

        if (! $product) {
            return;
        }

        $key = (string) $productId;
        $currentQty = $this->cart[$key]['quantity'] ?? 0;

        if ($currentQty + 1 > $product->quantity_on_hand) {
            $this->errorMessage = "Only {$product->quantity_on_hand} unit(s) of {$product->product_name} available.";

            return;
        }

        $this->cart[$key] = [
            'product_id' => $product->product_id,
            'name' => $product->product_name,
            'price' => (float) $product->unit_price,
            'quantity' => $currentQty + 1,
        ];

        $this->errorMessage = null;
        $this->syncCartSession();
    }

    public function removeFromCart(string $key): void
    {
        unset($this->cart[$key]);
        $this->syncCartSession();
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $product = Product::find($this->cart[$key]['product_id']);
        $quantity = max(1, $quantity);

        if ($product && $quantity > $product->quantity_on_hand) {
            $quantity = $product->quantity_on_hand;
        }

        $this->cart[$key]['quantity'] = $quantity;
        $this->syncCartSession();
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->discountType = 'none';
        $this->discountValue = 0;
        $this->payment = 'Cash';
        $this->amountReceived = 0;
        $this->errorMessage = null;

        session()->forget(['cart', 'discount_type', 'discount_value', 'payment', 'amount_received']);
    }

    public function setDiscountType(string $type): void
    {
        $this->discountType = $type;

        if ($type === 'none') {
            $this->discountValue = 0;
        }

        session(['discount_type' => $type, 'discount_value' => $this->discountValue]);
    }

    public function setPayment(string $method): void
    {
        $this->payment = $method;
        session(['payment' => $method]);
    }

    public function updatedDiscountValue(mixed $value): void
    {
        session(['discount_value' => $value]);
    }

    public function updatedAmountReceived(mixed $value): void
    {
        session(['amount_received' => $value]);
    }

    public function startNewSale(): void
    {
        $this->completedSale = null;
    }

    public function completeSale(): void
    {
        if (empty($this->cart)) {
            $this->errorMessage = 'Add at least one product before completing the sale.';

            return;
        }

        foreach ($this->cart as $item) {
            $product = Product::find($item['product_id']);

            if (! $product || $product->quantity_on_hand < $item['quantity']) {
                $this->errorMessage = "Not enough stock for {$item['name']}.";

                return;
            }
        }

        $subtotal = collect($this->cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $discountAmount = $this->calculateDiscount($subtotal);
        $total = max(0, $subtotal - $discountAmount);

        $paymentMethod = match ($this->payment) {
            'GCash' => PaymentMethod::GCash,
            'Cheque' => PaymentMethod::Cheque,
            default => PaymentMethod::Cash,
        };

        if ($paymentMethod === PaymentMethod::Cash) {
            if ($this->amountReceived < $total) {
                $this->errorMessage = 'Amount received is less than the total due.';

                return;
            }

            $amountReceived = $this->amountReceived;
            $changeAmount = $amountReceived - $total;
        } else {
            $amountReceived = $total;
            $changeAmount = 0;
        }

        $cartSnapshot = $this->cart;

        $sale = DB::transaction(function () use ($cartSnapshot, $subtotal, $discountAmount, $total, $paymentMethod, $amountReceived, $changeAmount) {
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

            foreach ($cartSnapshot as $item) {
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

        $this->completedSale = [
            'id' => (string) $sale->sale_id,
            'subtotal' => number_format($subtotal, 2),
            'discount' => number_format($discountAmount, 2),
            'total' => number_format($total, 2),
            'payment' => $this->payment,
            'received' => number_format($amountReceived, 2),
            'change' => number_format($changeAmount, 2),
        ];

        $this->cart = [];
        $this->discountType = 'none';
        $this->discountValue = 0;
        $this->payment = 'Cash';
        $this->amountReceived = 0;
        $this->errorMessage = null;

        session()->forget(['cart', 'discount_type', 'discount_value', 'payment', 'amount_received']);
    }

    private function syncCartSession(): void
    {
        session(['cart' => $this->cart]);
    }

    private function calculateDiscount(float $subtotal): float
    {
        return match ($this->discountType) {
            'percent' => round($subtotal * min(max($this->discountValue, 0), 100) / 100, 2),
            'fixed' => min(max($this->discountValue, 0), $subtotal),
            default => 0,
        };
    }

    public function render(): View
    {
        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($this->search !== '', fn ($query) => $query->where(
                'product_name', 'like', '%'.$this->search.'%'
            ))
            ->when($this->category !== '', fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('category_name', 'like', '%'.$this->category.'%')
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

        $subtotal = collect($this->cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
        $discountAmount = $this->calculateDiscount($subtotal);
        $total = max(0, $subtotal - $discountAmount);
        $vat = $total - ($total / 1.12);
        $change = $this->payment === 'Cash' && $this->amountReceived > 0 ? $this->amountReceived - $total : null;

        return view('livewire.cashier.pos', compact('products', 'subtotal', 'discountAmount', 'total', 'vat', 'change'));
    }
}
