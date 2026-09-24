<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\ReturnCondition;
use App\Enums\ReturnResolution;
use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\Warranty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->input('tab', 'returns') === 'warranty') {
            $warranties = Warranty::with('saleItem.product')
                ->latest('warranty_id')
                ->get()
                ->map(fn (Warranty $warranty) => (object) [
                    'id' => $warranty->warranty_id,
                    'product_name' => $warranty->saleItem->product->product_name ?? '—',
                    'issue' => null,
                    'purchase_date' => $warranty->start_date,
                    'warranty_until' => $warranty->end_date,
                    'created_at' => $warranty->start_date,
                    'status' => match ($warranty->claim_status->value) {
                        'claimed' => 'Pending',
                        'in_progress' => 'In Repair',
                        'resolved' => 'Approved',
                        default => 'Active',
                    },
                ]);

            return view('cashier.returns.index', ['warranties' => $warranties]);
        }

        $returns = ReturnRecord::with(['product', 'sale'])
            ->when($request->input('status') === 'Pending', fn ($query) => $query->where('status', 'open'))
            ->when($request->input('status') === 'Approved', fn ($query) => $query->where('status', 'resolved'))
            ->latest('return_date')
            ->get()
            ->map(fn (ReturnRecord $return) => (object) [
                'id' => $return->return_id,
                'transaction_id' => $return->sale_id,
                'transaction_code' => $return->sale?->code(),
                'product_name' => $return->product->product_name ?? '—',
                'reason' => $return->reason,
                'amount' => (float) ($return->product->unit_price ?? 0) * $return->quantity,
                'created_at' => $return->return_date,
                'processed_by' => null,
                'status' => $return->status->value === 'open' ? 'Pending' : 'Approved',
            ]);

        return view('cashier.returns.index', ['returns' => $returns]);
    }

    public function show(ReturnRecord $returnRecord): View
    {
        $returnRecord->load(['product', 'sale']);

        return view('cashier.returns.show', ['ret' => $returnRecord]);
    }

    public function process(Request $request): View
    {
        $transactionId = $request->input('transaction_id');
        $sale = $transactionId > 0
            ? Sale::where('status', SaleStatus::Completed)->with('items.product')->find($transactionId)
            : null;

        return view('cashier.returns.process', ['sale' => $sale, 'transactionId' => $transactionId]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sale_id' => ['required', 'exists:sale,sale_id'],
            'product_id' => ['required', 'exists:product,product_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'condition' => ['required', new Enum(ReturnCondition::class)],
            'resolution' => ['required', new Enum(ReturnResolution::class)],
        ]);

        ReturnRecord::create([
            'sale_id' => $validated['sale_id'],
            'product_id' => $validated['product_id'],
            'supplier_id' => null,
            'return_date' => now(),
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'condition' => $validated['condition'],
            'resolution' => $validated['resolution'],
            'status' => 'open',
        ]);

        return redirect()->route('cashier.returns.index')->with('success', 'Return recorded.');
    }

    public function warranty(Warranty $warranty): View
    {
        $warranty->load('saleItem.product');

        return view('cashier.returns.warranty', ['warranty' => $warranty]);
    }
}
