<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\SaleStatus;
use App\Enums\WarrantyClaimStatus;
use App\Enums\WarrantyOutcome;
use App\Models\AuditLog;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

trait FilesWarrantyClaims
{
    /**
     * Data for the "File Warranty Claim" screen: the transaction being looked up and its items that can still be claimed.
     *
     * @return array{sale: ?Sale, transactionId: int, items: Collection<int, SaleItem>}
     */
    protected function claimFormData(Request $request): array
    {
        $transactionId = (int) $request->input('transaction_id');

        $sale = $transactionId > 0
            ? Sale::where('status', SaleStatus::Completed)->with('items.product', 'items.warranty', 'items.sale')->find($transactionId)
            : null;

        return [
            'sale' => $sale,
            'transactionId' => $transactionId,
            'items' => $sale ? $sale->items->filter(fn (SaleItem $item) => $this->warrantyEndDate($item) !== null)->values() : collect(),
        ];
    }

    /**
     * The date a sold item's warranty runs until, or null when it has no warranty or the warranty has already expired.
     */
    protected function warrantyEndDate(SaleItem $item): ?Carbon
    {
        $days = (int) ($item->product->warranty_period_days ?? 0);

        if ($days <= 0 || ! $item->sale) {
            return null;
        }

        $ends = $item->sale->sale_date->copy()->startOfDay()->addDays($days);

        return $ends->greaterThanOrEqualTo(today()) ? $ends : null;
    }

    /**
     * Withdraw a claim that is still under review. The warranty itself stays, so the item can be claimed again.
     */
    protected function cancelWarrantyClaim(Warranty $warranty): bool
    {
        if (! $warranty->isCancellable()) {
            return false;
        }

        $warranty->update([
            'claim_status' => WarrantyClaimStatus::None,
            'claim_date' => null,
            'issue' => null,
            'outcome' => WarrantyOutcome::NotApplicable,
            'resolution_notes' => null,
        ]);

        AuditLog::record('warranty_outcome', 'Cancelled warranty claim WAR-'.str_pad((string) $warranty->warranty_id, 5, '0', STR_PAD_LEFT).'.');

        return true;
    }

    protected function storeWarrantyClaim(Request $request): Warranty
    {
        $validated = $request->validate([
            'sale_item_id' => ['required', 'exists:sale_item,sale_item_id'],
            'customer_name' => ['required', 'string', 'max:100'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'issue' => ['required', 'string', 'max:1000'],
        ]);

        $item = SaleItem::with('product', 'sale', 'warranty')->findOrFail($validated['sale_item_id']);
        $ends = $this->warrantyEndDate($item);

        if ($item->sale->status !== SaleStatus::Completed || $ends === null) {
            throw ValidationException::withMessages([
                'sale_item_id' => 'This item has no warranty, or its warranty has already expired.',
            ]);
        }

        $existing = $item->warranty;

        if ($existing && $existing->claim_status !== WarrantyClaimStatus::None) {
            throw ValidationException::withMessages([
                'sale_item_id' => 'A warranty claim was already filed for this item (WAR-'.str_pad((string) $existing->warranty_id, 5, '0', STR_PAD_LEFT).').',
            ]);
        }

        $details = [
            'customer_name' => $validated['customer_name'],
            'contact_number' => $validated['contact_number'] ?? null,
            'start_date' => $item->sale->sale_date->toDateString(),
            'end_date' => $ends->toDateString(),
            'claim_status' => WarrantyClaimStatus::Claimed,
            'claim_date' => today(),
            'issue' => $validated['issue'],
        ];

        $warranty = $existing
            ? tap($existing)->update($details)
            : Warranty::create($details + ['sale_item_id' => $item->sale_item_id, 'outcome' => WarrantyOutcome::NotApplicable]);

        AuditLog::record(
            'warranty_outcome',
            'Filed warranty claim WAR-'.str_pad((string) $warranty->warranty_id, 5, '0', STR_PAD_LEFT)." for {$item->product->product_name} (transaction {$item->sale->code()})."
        );

        return $warranty;
    }
}
