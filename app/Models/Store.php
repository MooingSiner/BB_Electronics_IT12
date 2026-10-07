<?php

namespace App\Models;

use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['store_name'])]
class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory;

    protected $table = 'store';

    protected $primaryKey = 'store_id';

    public $timestamps = false;

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'store_id', 'store_id');
    }
}
