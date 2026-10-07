<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Turns a cost price into a short letter code, e.g. 66.50 becomes "TT.SD" with the key CHRISTYNED.
 */
class CostCode
{
    public static function encode(float|string|null $cost, ?string $key = null): string
    {
        $key = strtoupper($key ?? (string) config('shop.cost_code_key'));

        if (strlen($key) !== 10 || count(array_unique(str_split($key))) !== 10) {
            throw new InvalidArgumentException('The cost code key must be 10 different letters.');
        }

        $amount = number_format((float) $cost, 2, '.', '');

        if (str_ends_with($amount, '.00')) {
            $amount = substr($amount, 0, -3);
        }

        return preg_replace_callback('/\d/', fn (array $digit) => $key[((int) $digit[0] + 9) % 10], $amount);
    }
}
