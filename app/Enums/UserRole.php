<?php

namespace App\Enums;

enum UserRole: string
{
    case OwnerManager = 'owner_manager';
    case Cashier = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::OwnerManager => 'Owner / Manager',
            self::Cashier => 'Cashier / Store Attendant',
        };
    }
}
