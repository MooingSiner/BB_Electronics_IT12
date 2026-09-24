<?php

namespace App\Models;

use App\Enums\WarrantyClaimStatus;
use App\Enums\WarrantyOutcome;
use Database\Factories\WarrantyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sale_item_id', 'customer_name', 'contact_number', 'start_date', 'end_date', 'claim_status', 'claim_date', 'outcome'])]
class Warranty extends Model
{
    /** @use HasFactory<WarrantyFactory> */
    use HasFactory;

    protected $table = 'warranty';

    protected $primaryKey = 'warranty_id';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'claim_date' => 'date',
            'claim_status' => WarrantyClaimStatus::class,
            'outcome' => WarrantyOutcome::class,
        ];
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class, 'sale_item_id', 'sale_item_id');
    }
}
