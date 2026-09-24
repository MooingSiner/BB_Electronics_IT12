<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'sale_date', 'subtotal', 'discount_amount', 'total_amount', 'payment_method', 'amount_paid', 'change_amount', 'status'])]
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
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
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
