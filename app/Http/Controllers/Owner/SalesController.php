<?php

namespace App\Http\Controllers\Owner;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Sale;
use App\Models\StockAdjustment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            ->latest('sale_date')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Sale $sale) => (object) [
                'id' => $sale->sale_id,
                'code' => $sale->code(),
                'products' => $sale->items->pluck('product.product_name')->filter()->implode(', '),
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

        $txn = (object) [
            'id' => $sale->sale_id,
            'code' => $sale->code(),
            'created_at' => $sale->sale_date,
            'status' => $sale->status === SaleStatus::Completed ? 'Completed' : 'Voided',
            'processed_by' => $sale->user->full_name ?? '—',
            'void_reason' => $sale->void_reason,
            'voided_at' => $sale->voided_at,
            'void_blocked_reason' => $sale->voidBlockedReason(),
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
            ]),
        ];

        return view('owner.sales.show', compact('txn'));
    }

    public function void(Request $request, Sale $sale): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:255']]);

        if ($blocked = $sale->voidBlockedReason()) {
            return back()->with('error', $blocked);
        }

        DB::transaction(function () use ($sale, $validated) {
            foreach ($sale->items as $item) {
                StockAdjustment::create([
                    'product_id' => $item->product_id,
                    'user_id' => Auth::id(),
                    'adjustment_date' => now(),
                    'quantity_change' => $item->quantity,
                    'reason' => "Restocked from voided sale {$sale->code()}",
                ]);
            }

            $sale->update([
                'status' => SaleStatus::Voided,
                'void_reason' => $validated['reason'],
                'voided_at' => now(),
            ]);

            AuditLog::record('void', "Voided sale {$sale->code()}: {$validated['reason']}");
        });

        return back()->with('success', 'Sale voided and its items were put back in stock.');
    }

    public function receipt(Sale $sale): RedirectResponse
    {
        return redirect()->route('owner.sales.show', $sale->sale_id);
    }
}
