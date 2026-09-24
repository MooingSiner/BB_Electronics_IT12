<?php

namespace App\Http\Controllers\Owner;

use App\Enums\ReturnCondition;
use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use App\Enums\WarrantyClaimStatus;
use App\Enums\WarrantyOutcome;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ReturnRecord;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\Warranty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                    'transaction_id' => $warranty->saleItem->sale?->code() ?? '—',
                    'product_name' => $warranty->saleItem->product->product_name ?? '—',
                    'warranty_ref' => 'WAR-'.str_pad((string) $warranty->warranty_id, 5, '0', STR_PAD_LEFT),
                    'issue' => $warranty->claim_status !== WarrantyClaimStatus::None ? 'Claim filed' : '—',
                    'created_at' => $warranty->start_date,
                    'status' => $this->warrantyStatusLabel($warranty->claim_status),
                ]);

            return view('owner.returns.index', ['returns' => collect(), 'warranties' => $warranties]);
        }

        $returns = ReturnRecord::whereNotNull('sale_id')
            ->with(['product', 'sale'])
            ->when($request->input('status') === 'Pending', fn ($query) => $query->where('status', ReturnStatus::Open))
            ->when($request->input('status') === 'Completed', fn ($query) => $query->where('status', ReturnStatus::Resolved))
            ->latest('return_date')
            ->get()
            ->map(fn (ReturnRecord $return) => (object) [
                'id' => $return->return_id,
                'transaction_id' => $return->sale?->code() ?? '—',
                'product_name' => $return->product->product_name ?? '—',
                'qty' => $return->quantity,
                'reason' => $return->reason,
                'resolution' => ucwords(str_replace('_', ' ', $return->resolution->value)),
                'created_at' => $return->return_date,
                'status' => $return->status === ReturnStatus::Open ? 'Pending' : 'Completed',
            ]);

        return view('owner.returns.index', ['returns' => $returns, 'warranties' => collect()]);
    }

    public function show(ReturnRecord $returnRecord): View
    {
        $returnRecord->load(['product', 'sale']);

        $return = (object) [
            'id' => $returnRecord->return_id,
            'transaction_id' => $returnRecord->sale?->code(),
            'transaction_sale_id' => $returnRecord->sale_id,
            'product_name' => $returnRecord->product->product_name ?? '—',
            'product_code' => $returnRecord->product->product_code ?? '—',
            'qty' => $returnRecord->quantity,
            'reason' => $returnRecord->reason,
            'condition' => ucwords(str_replace('_', ' ', $returnRecord->condition->value)),
            'condition_value' => $returnRecord->condition->value,
            'resolution' => ucwords(str_replace('_', ' ', $returnRecord->resolution->value)),
            'resolution_value' => $returnRecord->resolution->value,
            'created_at' => $returnRecord->return_date,
            'status' => $returnRecord->status === ReturnStatus::Open ? 'Pending' : 'Completed',
            'status_value' => $returnRecord->status->value,
        ];

        return view('owner.returns.show', compact('return'));
    }

    public function process(?ReturnRecord $returnRecord = null): View
    {
        $transactionId = request('transaction_id');
        $sale = $transactionId ? Sale::with('items.product')->find($transactionId) : null;

        $txn = $sale ? (object) [
            'id' => $sale->sale_id,
            'code' => $sale->code(),
            'items' => $sale->items->map(fn ($item) => (object) [
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name ?? '—',
                'qty' => $item->quantity,
            ]),
        ] : null;

        return view('owner.returns.process', compact('txn'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'exists:sale,sale_id'],
            'product_id' => ['required', 'exists:product,product_id'],
            'qty' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'resolution' => ['required', new Enum(ReturnResolution::class)],
            'condition' => ['required', new Enum(ReturnCondition::class)],
        ]);

        $return = ReturnRecord::create([
            'sale_id' => $validated['transaction_id'],
            'product_id' => $validated['product_id'],
            'supplier_id' => null,
            'return_date' => now(),
            'quantity' => $validated['qty'],
            'reason' => $validated['reason'],
            'condition' => $validated['condition'],
            'resolution' => $validated['resolution'],
            'status' => ReturnStatus::Resolved,
        ]);

        $restockable = in_array($validated['condition'], [
            ReturnCondition::WrongItem->value,
            ReturnCondition::CustomerChangedMind->value,
            ReturnCondition::Other->value,
        ], true);

        if ($restockable) {
            StockAdjustment::create([
                'product_id' => $validated['product_id'],
                'user_id' => Auth::id(),
                'adjustment_date' => now(),
                'quantity_change' => $validated['qty'],
                'reason' => "Restocked from return #{$return->return_id}",
            ]);
        }

        AuditLog::record(
            'refund',
            "Processed return #{$return->return_id} for {$validated['qty']} unit(s) — resolution: ".ucwords(str_replace('_', ' ', $validated['resolution']))
        );

        return redirect()->route('owner.returns.show', $return->return_id)->with('success', 'Return processed.');
    }

    public function resolve(ReturnRecord $returnRecord): RedirectResponse
    {
        $returnRecord->update(['status' => ReturnStatus::Resolved]);

        AuditLog::record('refund', "Marked return #{$returnRecord->return_id} as resolved.");

        return redirect()->route('owner.returns.show', $returnRecord->return_id)->with('success', 'Return marked as resolved.');
    }

    public function create(Sale $transaction): RedirectResponse
    {
        return redirect()->route('owner.returns.process', ['transaction_id' => $transaction->sale_id]);
    }

    public function warranty(Warranty $warranty): View
    {
        $warranty->load('saleItem.product', 'saleItem.sale');

        $item = (object) [
            'id' => $warranty->warranty_id,
            'warranty_ref' => 'WAR-'.str_pad((string) $warranty->warranty_id, 5, '0', STR_PAD_LEFT),
            'transaction_id' => $warranty->saleItem->sale_id ?? null,
            'transaction_code' => $warranty->saleItem->sale?->code(),
            'productName' => $warranty->saleItem->product->product_name ?? '—',
            'warranty_start' => $warranty->start_date,
            'warranty_end' => $warranty->end_date,
            'created_at' => $warranty->start_date,
            'claim_date' => $warranty->claim_date,
            'status' => $this->warrantyStatusLabel($warranty->claim_status),
            'status_value' => $warranty->claim_status->value,
            'customerName' => $warranty->customer_name,
            'contactNumber' => $warranty->contact_number,
            'issue' => $warranty->claim_status !== WarrantyClaimStatus::None ? 'Customer filed a warranty claim.' : 'No claim filed yet.',
            'resolution' => $warranty->outcome && $warranty->outcome !== WarrantyOutcome::NotApplicable
                ? ucwords(str_replace('_', ' ', $warranty->outcome->value))
                : null,
        ];

        return view('owner.returns.warranty', ['warranty' => $item]);
    }

    public function warrantyUpdate(Request $request, Warranty $warranty): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', new Enum(WarrantyClaimStatus::class)],
            'outcome' => ['nullable', new Enum(WarrantyOutcome::class)],
        ]);

        $warranty->update([
            'claim_status' => $validated['status'],
            'claim_date' => $validated['status'] !== WarrantyClaimStatus::None->value ? ($warranty->claim_date ?? now()) : $warranty->claim_date,
            'outcome' => $validated['outcome'] ?? $warranty->outcome,
        ]);

        AuditLog::record(
            'warranty_outcome',
            "Updated warranty #{$warranty->warranty_id} — status: ".ucwords(str_replace('_', ' ', $validated['status'])).
                (($validated['outcome'] ?? null) ? ', outcome: '.ucwords(str_replace('_', ' ', $validated['outcome'])) : '')
        );

        return redirect()->route('owner.returns.warranty', $warranty->warranty_id)->with('success', 'Warranty claim updated.');
    }

    private function warrantyStatusLabel(WarrantyClaimStatus $status): string
    {
        return match ($status) {
            WarrantyClaimStatus::None => 'Active',
            WarrantyClaimStatus::Claimed => 'Under Review',
            WarrantyClaimStatus::InProgress => 'Repaired',
            WarrantyClaimStatus::Resolved => 'Completed',
        };
    }
}
