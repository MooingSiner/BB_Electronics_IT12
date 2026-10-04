<?php

namespace App\Enums;

enum ReturnCondition: string
{
    case Defective = 'defective';
    case Damaged = 'damaged';
    case WrongItem = 'wrong_item';
    case CustomerChangedMind = 'customer_changed_mind';
    case Other = 'other';

    /**
     * Whether a returned item in this condition can be sold again, so it goes back into stock.
     */
    public function isRestockable(): bool
    {
        return match ($this) {
            self::WrongItem, self::CustomerChangedMind, self::Other => true,
            self::Defective, self::Damaged => false,
        };
    }
}
