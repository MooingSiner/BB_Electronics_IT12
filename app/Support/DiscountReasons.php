<?php

namespace App\Support;

class DiscountReasons
{
    public const OTHER = 'Other';

    /**
     * @return list<string>
     */
    public static function options(): array
    {
        return [
            'Senior citizen',
            'PWD',
            'Regular or loyal customer',
            'Bulk purchase',
            'Price match or customer negotiated',
            'Slightly damaged or display unit',
            'Promo or sale event',
            'Owner approved',
            self::OTHER,
        ];
    }

    /**
     * The text saved with the sale, or null when the choice is missing or "Other" has no note.
     */
    public static function resolve(string $reason, string $note): ?string
    {
        if (! in_array($reason, self::options(), true)) {
            return null;
        }

        if ($reason !== self::OTHER) {
            return $reason;
        }

        $note = trim($note);

        return $note === '' ? null : self::OTHER.': '.mb_substr($note, 0, 60);
    }
}
