<?php

namespace App\Models;

use App\Enums\StockStatus;
use Database\Factories\InventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'quantity'])]
class Inventory extends Model
{
    /** @use HasFactory<InventoryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_status' => StockStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Inventory $inventory) {
            $reorderLevel = $inventory->product?->reorder_level
                ?? Product::query()->find($inventory->product_id)?->reorder_level
                ?? 0;

            $inventory->stock_status = match (true) {
                $inventory->quantity <= 0 => StockStatus::OutOfStock,
                $inventory->quantity <= $reorderLevel => StockStatus::LowStock,
                default => StockStatus::InStock,
            };
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
