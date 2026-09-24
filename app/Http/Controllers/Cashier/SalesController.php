<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function index(Request $request): View
    {
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
            ->when($request->input('date') === 'today', fn ($query) => $query->whereDate('sale_date', today()))
            ->when($request->boolean('mine'), fn ($query) => $query->where('user_id', Auth::id()))
            ->latest('sale_date')
            ->get()
            ->map(fn (Sale $sale) => $this->present($sale));

        return view('cashier.sales.index', compact('transactions'));
    }

    public function show(Sale $sale): View
    {
        $sale->load(['user', 'items.product']);

        $txn = $this->present($sale);

        return view('cashier.sales.show', compact('txn'));
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['user', 'items.product']);

        $txn = $this->present($sale);

        return view('cashier.sales.receipt', compact('txn'));
    }

    private function present(Sale $sale): object
    {
        return (object) [
            'id' => $sale->sale_id,
            'code' => $sale->code(),
            'items_summary' => $sale->items->pluck('product.product_name')->filter()->implode(', '),
            'total_qty' => $sale->items->sum('quantity'),
            'total' => (float) $sale->total_amount,
            'subtotal' => (float) $sale->subtotal,
            'discount_amount' => (float) $sale->discount_amount,
            'payment_method' => $sale->payment_method->label(),
            'amount_paid' => (float) $sale->amount_paid,
            'change_amount' => (float) $sale->change_amount,
            'created_at' => $sale->sale_date,
            'processed_by' => $sale->user->full_name ?? '—',
            'status' => $sale->status === SaleStatus::Completed ? 'Completed' : 'Voided',
            'items' => $sale->items->map(fn ($item) => (object) [
                'name' => $item->product->product_name ?? '—',
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'subtotal' => (float) $item->subtotal,
            ]),
        ];
    }
}
