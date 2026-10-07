<?php

namespace App\Models;

use Database\Factories\StockAdjustmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

#[Fillable(['product_id', 'user_id', 'adjustment_date', 'quantity_change', 'reason'])]
class StockAdjustment extends Model
{
    /** @use HasFactory<StockAdjustmentFactory> */
    use HasFactory;

    protected $table = 'stock_adjustment';

    protected $primaryKey = 'adjustment_id';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'adjustment_date' => 'datetime',
            'quantity_change' => 'integer',
        ];
    }

    /**
     * Reasons the system writes itself. Only entries typed in by the owner (Stock In and Stock Out) can be reversed.
     *
     * @var list<string>
     */
    private const SYSTEM_REASON_PREFIXES = [
        'Restocked from return',
        'Restocked from voided sale',
        'Replacement issued for return',
        'Replacement received from',
        'Returned to supplier',
        'Returned to store',
        'Reversal of #',
    ];

    public function isManualEntry(): bool
    {
        foreach (self::SYSTEM_REASON_PREFIXES as $prefix) {
            if (str_starts_with((string) $this->reason, $prefix)) {
                return false;
            }
        }

        return true;
    }

    /**
     * The id of the entry that a reversal entry undoes, or null when this is not a reversal.
     */
    public static function reversedIdFrom(?string $reason): ?int
    {
        return preg_match('/^Reversal of #(\d+)/', (string) $reason, $match) === 1 ? (int) $match[1] : null;
    }

    public function isReversed(): bool
    {
        return static::where('reason', 'like', "Reversal of #{$this->adjustment_id}:%")->exists();
    }

    /**
     * Why this entry cannot be reversed, or null when it can.
     *
     * @param  ?Collection<int, int>  $reversedIds  ids already known to be reversed (saves one query per row)
     */
    public function reverseBlockedReason(?Collection $reversedIds = null): ?string
    {
        if (! $this->isManualEntry()) {
            return 'Only a Stock In or Stock Out entered by hand can be reversed.';
        }

        $alreadyReversed = $reversedIds ? $reversedIds->contains($this->adjustment_id) : $this->isReversed();

        if ($alreadyReversed) {
            return 'This entry was already reversed.';
        }

        if ($this->quantity_change > 0 && (int) $this->product->quantity_on_hand < $this->quantity_change) {
            return 'Not enough stock is left to take these units out again.';
        }

        return null;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
