<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_id', 'product_code', 'barcode', 'product_name', 'image_url', 'unit_price', 'cost_price', 'quantity_on_hand', 'reorder_level', 'warranty_period_days', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $table = 'product';

    protected $primaryKey = 'product_id';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'quantity_on_hand' => 'integer',
            'reorder_level' => 'integer',
            'warranty_period_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'product_id', 'product_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class, 'product_id', 'product_id');
    }

    public function returnRecords(): HasMany
    {
        return $this->hasMany(ReturnRecord::class, 'product_id', 'product_id');
    }

    /**
     * Find an active product by what a scanner or the cashier typed: its barcode or its product code.
     */
    public static function findByScan(string $value): ?self
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return static::where('is_active', true)
            ->where(fn ($query) => $query->where('barcode', $value)->orWhere('product_code', $value))
            ->first();
    }

    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_level;
    }

    public static function generateCode(Category $category): string
    {
        $prefix = substr(strtoupper(preg_replace('/[^A-Za-z]/', '', $category->category_name)), 0, 3) ?: 'GEN';

        do {
            $sequence = static::where('product_code', 'like', "{$prefix}-%")->count() + 1;
            $code = sprintf('%s-%04d', $prefix, $sequence);
        } while (static::where('product_code', $code)->exists());

        return $code;
    }
}
