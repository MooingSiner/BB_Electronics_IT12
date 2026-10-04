<?php

namespace App\Models;

use App\Enums\ReturnCondition;
use App\Enums\ReturnResolution;
use App\Enums\ReturnStatus;
use Database\Factories\ReturnRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sale_id', 'product_id', 'supplier_id', 'order_id', 'return_date', 'quantity', 'reason', 'condition', 'resolution', 'status'])]
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
     * Only a customer return that is still pending can be cancelled; nothing has touched stock yet.
     */
    public function isCancellable(): bool
    {
        return $this->sale_id !== null && $this->status === ReturnStatus::Open;
    }

    public function stockNote(): string
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
