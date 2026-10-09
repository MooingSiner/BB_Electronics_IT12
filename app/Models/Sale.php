<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use App\Enums\SaleStatus;
use App\Enums\WarrantyClaimStatus;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['user_id', 'sale_date', 'subtotal', 'discount_amount', 'discount_reason', 'total_amount', 'payment_method', 'amount_paid', 'change_amount', 'status', 'void_reason', 'voided_at'])]
class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    protected $table = 'sale';

    protected $primaryKey = 'sale_id';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_date' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'amount_paid' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'status' => SaleStatus::class,
            'voided_at' => 'datetime',
        ];
    }

    /**
     * Why this sale can't be voided, or null when it can. A sale with returns or an open warranty claim has
     * already been partly undone or relied on, so voiding it as well would count the same units twice.
     */
    public function voidBlockedReason(): ?string
    {
        if ($this->status !== SaleStatus::Completed) {
            return 'This sale was already voided.';
        }

        if ($this->returnRecords()->exists()) {
            return 'This sale already has a return recorded against it, so it can no longer be voided.';
        }

        $hasOpenClaim = Warranty::whereIn('claim_status', [WarrantyClaimStatus::Claimed, WarrantyClaimStatus::InProgress])
            ->whereHas('saleItem', fn ($query) => $query->where('sale_id', $this->sale_id))
            ->exists();

        return $hasOpenClaim ? 'A warranty claim is open on an item from this sale, so it can no longer be voided.' : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Money refunded per sale: resolved customer returns whose resolution is a refund.
     *
     * @param  iterable<int>|null  $saleIds
     * @return Collection<int, float>
     */
    public static function refundsBySale(?iterable $saleIds = null): Collection
    {
        return ReturnRecord::query()
            ->whereNotNull('sale_id')
            ->where('status', ReturnStatus::Resolved)
            ->where(fn ($query) => $query
                ->where('resolution', ReturnResolution::Refund)
                ->orWhere(fn ($exchange) => $exchange->where('resolution', ReturnResolution::Replacement)->whereNotNull('price_difference')))
            ->when($saleIds !== null, fn ($query) => $query->whereIn('sale_id', collect($saleIds)->all()))
            ->with('sale.items')
            ->get()
            ->groupBy('sale_id')
            ->map(fn (Collection $returns) => round($returns->sum(fn (ReturnRecord $return) => $return->moneyBack()), 2));
    }

    /**
     * Revenue of the given completed sales after taking off what was refunded.
     *
     * @param  Builder<Sale>  $completedSales
     */
    public static function netRevenue(Builder $completedSales): float
    {
        $rows = $completedSales->get(['sale_id', 'total_amount']);

        return round((float) $rows->sum('total_amount') - (float) static::refundsBySale($rows->pluck('sale_id'))->sum(), 2);
    }

    public function refundedAmount(): float
    {
        $returns = $this->relationLoaded('returnRecords') ? $this->returnRecords : $this->returnRecords()->get();

        return round((float) $returns->sum(fn (ReturnRecord $return) => $return->setRelation('sale', $this)->moneyBack()), 2);
    }

    public function netTotal(): float
    {
        return round((float) $this->total_amount - $this->refundedAmount(), 2);
    }

    /**
     * "Returned", "Partly returned" or "Return pending" for sales with customer returns, otherwise null.
     */
    public function returnStatusLabel(): ?string
    {
        $returns = $this->relationLoaded('returnRecords') ? $this->returnRecords : $this->returnRecords()->get();

        if ($returns->isEmpty()) {
            return null;
        }

        if ($returns->every(fn (ReturnRecord $return) => $return->status === ReturnStatus::Open)) {
            return 'Return pending';
        }

        $sold = $this->relationLoaded('items') ? $this->items->sum('quantity') : $this->items()->sum('quantity');

        return $returns->sum('quantity') >= $sold ? 'Returned' : 'Partly returned';
    }

    /**
     * @return Collection<int, object>
     */
    public function returnLines(): Collection
    {
        $this->loadMissing('returnRecords.replacementProduct');

        return $this->returnRecords->map(fn (ReturnRecord $return) => (object) [
            'id' => $return->return_id,
            'product' => $return->product->product_name ?? '—',
            'quantity' => $return->quantity,
            'condition' => ucwords(str_replace('_', ' ', $return->condition->value)),
            'resolution' => $return->resolution === ReturnResolution::Replacement && $return->status === ReturnStatus::Resolved
                ? 'Replaced'
                : ucwords(str_replace('_', ' ', $return->resolution->value)),
            'status' => $return->status === ReturnStatus::Open ? 'Pending' : 'Resolved',
            'exchange' => $return->isExchange()
                ? 'Exchanged for '.($return->replacementProduct->product_name ?? 'another product').' ×'.$return->quantity
                : null,
            'refund' => $return->status === ReturnStatus::Resolved && ($return->resolution === ReturnResolution::Refund || $return->isExchange())
                ? $return->setRelation('sale', $this)->moneyBack()
                : null,
        ]);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'sale_id');
    }

    public function returnRecords(): HasMany
    {
        return $this->hasMany(ReturnRecord::class, 'sale_id', 'sale_id');
    }

    public function code(): string
    {
        return static::formatCode($this->sale_id);
    }

    public static function formatCode(int $saleId): string
    {
        return sprintf('TXN-%05d', $saleId);
    }
}
