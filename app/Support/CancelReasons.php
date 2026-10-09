<?php

namespace App\Support;

class CancelReasons
{
    public const OTHER = 'Other';

    /**
     * @return list<string>
     */
    public static function options(): array
    {
        return [
            'Ordered by mistake',
            'Wrong supplier or store',
            'Duplicate order',
            'Price changed',
            'Supplier is out of stock',
            'No longer needed',
            self::OTHER,
        ];
    }

    /**
     * The text saved with the order, or null when the choice is missing or "Other" has no note.
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
