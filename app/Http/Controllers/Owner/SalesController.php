<?php

namespace App\Http\Controllers\Owner;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(Request $request): View
    {
        $movement = $this->movementByProduct();

        $transactions = Sale::query()
            ->with(['user', 'items.product'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');

                $query->where(fn ($q) => $q
                    ->where('sale_id', 'like', "%{$search}%")
                    ->orWhereHas('items.product', fn ($p) => $p->where('product_name', 'like', "%{$search}%")));
            })
            ->when($request->input('status') === 'Completed', fn ($query) => $query->where('status', SaleStatus::Completed))
            ->when($request->input('status') === 'Voided', fn ($query) => $query->where('status', SaleStatus::Voided))
            ->when($request->input('discount') === 'with', fn ($query) => $query->where('discount_amount', '>', 0))
            ->when($request->input('discount') === 'without', fn ($query) => $query->where('discount_amount', 0))
            ->latest('sale_date')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Sale $sale) => (object) [
                'id' => $sale->sale_id,
                'code' => $sale->code(),
                'products' => $sale->items->map(fn ($item) => (object) [
                    'name' => $item->product->product_name ?? '—',
                    'movement' => $this->movementLabel($item->product_id, $movement),
                ]),
                'qty' => $sale->items->sum('quantity'),
                'total' => '₱'.number_format((float) $sale->total_amount, 2),
                'discount' => (float) $sale->subtotal > 0 ? round(((float) $sale->discount_amount / (float) $sale->subtotal) * 100) : 0,
                'payment_method' => $sale->payment_method->label(),
                'date' => $sale->sale_date->format('M d, Y'),
                'processed_by' => $sale->user->full_name ?? '—',
                'status' => $sale->status === SaleStatus::Completed ? 'Completed' : 'Voided',
            ]);

        return view('owner.sales.index', compact('transactions'));
    }

    public function show(Sale $sale): View
    {
        $sale->load(['user', 'items.product']);

        $movement = $this->movementByProduct();

        $txn = (object) [
            'id' => $sale->sale_id,
            'code' => $sale->code(),
            'created_at' => $sale->sale_date,
            'status' => $sale->status === SaleStatus::Completed ? 'Completed' : 'Voided',
            'processed_by' => $sale->user->full_name ?? '—',
            'payment_method' => $sale->payment_method->label(),
            'amount_received' => (float) $sale->amount_paid,
            'change_given' => (float) $sale->change_amount,
            'discount_pct' => (float) $sale->subtotal > 0 ? round(((float) $sale->discount_amount / (float) $sale->subtotal) * 100) : 0,
            'subtotal' => (float) $sale->subtotal,
            'discount_amount' => (float) $sale->discount_amount,
            'total_amount' => (float) $sale->total_amount,
            'items' => $sale->items->map(fn ($item) => (object) [
                'product_name' => $item->product->product_name ?? '—',
                'product_id' => $item->product->product_code ?? $item->product_id,
                'qty' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
                'movement' => $this->movementLabel($item->product_id, $movement),
            ]),
        ];

        return view('owner.sales.show', compact('txn'));
    }

    public function receipt(Sale $sale): RedirectResponse
    {
        return redirect()->route('owner.sales.show', $sale->sale_id);
    }

    /**
     * Units sold per product over the last 30 days, keyed by product_id.
     */
    private function movementByProduct(): Collection
    {
        return Sale::where('status', SaleStatus::Completed)
            ->where('sale_date', '>=', now()->subDays(30)->startOfDay())
            ->with('items')
            ->get()
            ->flatMap->items
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'));
    }

    private function movementLabel(?int $productId, Collection $movement): string
    {
        $unitsSold = (int) ($movement[$productId] ?? 0);

        return $unitsSold === 0 ? 'No Movement' : ($unitsSold >= 10 ? 'Fast-Moving' : 'Slow-Moving');
    }
}
