<?php

namespace App\Models;

use App\Enums\ReturnCondition;
use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use Database\Factories\ReturnRecordFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Fillable(['sale_id', 'product_id', 'supplier_id', 'order_id', 'replacement_product_id', 'price_difference', 'return_date', 'quantity', 'reason', 'condition', 'resolution', 'status'])]
class ReturnRecord extends Model
{
    /** @use HasFactory<ReturnRecordFactory> */
    use HasFactory;

    protected $table = 'return_record';

    protected $primaryKey = 'return_id';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'return_date' => 'datetime',
            'quantity' => 'integer',
            'price_difference' => 'decimal:2',
            'condition' => ReturnCondition::class,
            'resolution' => ReturnResolution::class,
            'status' => ReturnStatus::class,
        ];
    }

    /**
     * A customer return puts the item back in stock when it is in a sellable condition.
     */
    public function willRestock(): bool
    {
        return $this->sale_id !== null && $this->condition->isRestockable();
    }

    /**
     * A replacement hands the customer a new unit, so those units leave stock when the return is resolved.
     */
    public function replacementUnits(): int
    {
        return $this->sale_id !== null && $this->resolution === ReturnResolution::Replacement ? $this->quantity : 0;
    }

    /**
     * Only a customer return that is still pending can be cancelled; nothing has touched stock yet.
     */
    public function isCancellable(): bool
    {
        return $this->sale_id !== null && $this->status === ReturnStatus::Open;
    }

    /**
     * The money handed back for this return: the units' share of what the customer paid, discount included.
     */
    public function refundAmount(): float
    {
        $sale = $this->sale;

        if (! $sale) {
            return 0.0;
        }

        $unitPrice = (float) ($sale->items->firstWhere('product_id', $this->product_id)->unit_price ?? 0);
        $paidShare = (float) $sale->subtotal > 0 ? (float) $sale->total_amount / (float) $sale->subtotal : 1.0;

        return round($this->quantity * $unitPrice * $paidShare, 2);
    }

    /**
     * Money that went back to the customer for this resolved return (negative when they paid extra for an exchange).
     */
    public function moneyBack(): float
    {
        if ($this->status !== ReturnStatus::Resolved) {
            return 0.0;
        }

        return match (true) {
            $this->resolution === ReturnResolution::Refund => $this->refundAmount(),
            $this->isExchange() && $this->price_difference !== null => -(float) $this->price_difference,
            default => 0.0,
        };
    }

    /**
     * Whether the customer gets a different product than the one they brought back.
     */
    public function isExchange(): bool
    {
        return $this->replacementUnits() > 0
            && $this->replacement_product_id !== null
            && (int) $this->replacement_product_id !== (int) $this->product_id;
    }

    public function givenProductId(): int
    {
        return $this->isExchange() ? (int) $this->replacement_product_id : (int) $this->product_id;
    }

    /**
     * What the customer still owes (positive) or gets back (negative) for an exchange. Uses the stored amount once
     * resolved, otherwise today's price of the new product against what they paid for the returned units.
     */
    public function exchangeDifference(): float
    {
        if (! $this->isExchange()) {
            return 0.0;
        }

        if ($this->price_difference !== null) {
            return (float) $this->price_difference;
        }

        return round((float) ($this->replacementProduct->unit_price ?? 0) * $this->quantity - $this->refundAmount(), 2);
    }

    public function exchangeDifferenceNote(): ?string
    {
        if (! $this->isExchange()) {
            return null;
        }

        $difference = $this->exchangeDifference();

        return match (true) {
            $difference > 0 => 'Customer pays ₱'.number_format($difference, 2).' for the exchange.',
            $difference < 0 => 'Give the customer ₱'.number_format(abs($difference), 2).' back for the exchange.',
            default => 'Even exchange, no money changes hands.',
        };
    }

    /**
     * Finish the return: stock moves, the exchange price difference is fixed, and the status becomes resolved.
     * Throws a DomainException, changing nothing, when the replacement units are not in stock.
     */
    public function settle(): string
    {
        $restock = $this->willRestock();
        $units = $this->replacementUnits();

        DB::transaction(function () use ($restock, $units) {
            if ($units > 0) {
                $given = Product::whereKey($this->givenProductId())->lockForUpdate()->firstOrFail();

                if ($given->quantity_on_hand < $units) {
                    throw new DomainException("Only {$given->quantity_on_hand} unit(s) of {$given->product_name} are in stock, so {$units} replacement unit(s) can't be given yet. Stock in more first.");
                }
            }

            $this->price_difference = $this->isExchange() ? $this->exchangeDifference() : null;
            $this->status = ReturnStatus::Resolved;
            $this->save();

            if ($units > 0) {
                StockAdjustment::create([
                    'product_id' => $this->givenProductId(),
                    'user_id' => Auth::id(),
                    'adjustment_date' => now(),
                    'quantity_change' => -$units,
                    'reason' => "Replacement issued for return #{$this->return_id}",
                ]);
            }

            if ($restock) {
                StockAdjustment::create([
                    'product_id' => $this->product_id,
                    'user_id' => Auth::id(),
                    'adjustment_date' => now(),
                    'quantity_change' => $this->quantity,
                    'reason' => "Restocked from return #{$this->return_id}",
                ]);
            }
        });

        $message = $restock ? "{$this->quantity} unit(s) added back to stock." : 'The item was not added back to stock.';

        if ($units > 0) {
            $what = $this->isExchange() ? ' of '.($this->replacementProduct->product_name ?? 'the new product') : '';
            $message .= " {$units} unit(s){$what} taken out of stock for the ".($this->isExchange() ? 'exchange' : 'replacement').'.';
        }

        if ($note = $this->exchangeDifferenceNote()) {
            $message .= ' '.$note;
        }

        return $message;
    }

    public function replacementProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'replacement_product_id', 'product_id');
    }

    public function stockNote(): string
    {
        $note = $this->returnedItemNote();

        if ($this->replacementUnits() === 0) {
            return $note;
        }

        $issued = StockAdjustment::where('product_id', $this->givenProductId())
            ->where('reason', "Replacement issued for return #{$this->return_id}")
            ->first();

        $what = $this->isExchange() ? ' of '.($this->replacementProduct->product_name ?? 'the new product') : '';
        $for = $this->isExchange() ? 'exchange' : 'replacement';

        $replacement = $issued
            ? abs($issued->quantity_change)." unit(s){$what} taken out of stock for the {$for} on ".$issued->adjustment_date->format('M d, Y').'.'
            : ($this->status === ReturnStatus::Open
                ? "{$this->replacementUnits()} unit(s){$what} will be taken out of stock for the {$for} when the owner marks it resolved."
                : "No stock change was recorded for the {$for}.");

        return $note.' '.$replacement;
    }

    private function returnedItemNote(): string
    {
        $added = StockAdjustment::where('product_id', $this->product_id)
            ->where('reason', "Restocked from return #{$this->return_id}")
            ->first();

        if ($added) {
            return "{$added->quantity_change} unit(s) added back to stock on ".$added->adjustment_date->format('M d, Y').'.';
        }

        $condition = str_replace('_', ' ', $this->condition->value);

        return match (true) {
            $this->sale_id === null => 'Not a customer return, so stock is not changed here.',
            $this->status === ReturnStatus::Open && $this->willRestock() => 'Will be added back to stock when the owner marks it resolved.',
            $this->status === ReturnStatus::Open => "Will not be added back to stock, because the item is {$condition}.",
            $this->willRestock() => 'No stock change was recorded for this return.',
            default => "Not added back to stock, because the item is {$condition}.",
        };
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'sale_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'order_id', 'order_id');
    }
}
