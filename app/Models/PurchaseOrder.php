<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use Database\Factories\PurchaseOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['supplier_id', 'store_id', 'user_id', 'order_date', 'invoice_number', 'status', 'is_archived', 'date_received', 'cancel_reason'])]
class PurchaseOrder extends Model
{
    /** @use HasFactory<PurchaseOrderFactory> */
    use HasFactory;

    protected $table = 'purchase_order';

    protected $primaryKey = 'order_id';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'date_received' => 'datetime',
            'status' => PurchaseOrderStatus::class,
            'is_archived' => 'boolean',
        ];
    }

    /**
     * Why this order can't be cancelled, or null when it can. Once anything is received, stock has changed.
     */
    public function cancelBlockedReason(): ?string
    {
        if ($this->status === PurchaseOrderStatus::Cancelled) {
            return 'This order is already cancelled.';
        }

        if ($this->status !== PurchaseOrderStatus::Pending || $this->items()->where('quantity_received', '>', 0)->exists()) {
            return 'Part of this order was already received, so it can no longer be cancelled.';
        }

        if (ReturnRecord::where('order_id', $this->order_id)->exists()) {
            return 'This order has damage reports, so it can no longer be cancelled.';
        }

        return null;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'store_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }
}
