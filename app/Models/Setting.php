<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['setting_key', 'setting_value'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    public const VAT_RATE = 'vat_rate';

    protected $table = 'setting';

    protected $primaryKey = 'setting_key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    /**
     * The VAT percentage added to every sale. The owner changes it in Shop Settings; until then it is the default in config/shop.php.
     */
    public static function vatRate(): float
    {
        $value = static::query()->whereKey(self::VAT_RATE)->value('setting_value');

        return round(max(0, min(100, (float) ($value ?? config('shop.vat_rate')))), 2);
    }

    public static function setVatRate(float $rate): void
    {
        static::query()->updateOrCreate(['setting_key' => self::VAT_RATE], ['setting_value' => (string) round($rate, 2)]);
    }
}
