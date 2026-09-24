<?php

namespace App\Enums;

enum UserRole: string
{
    case OwnerManager = 'owner_manager';
    case CashierAttendant = 'cashier_attendant';

    public function label(): string
    {
        return match ($this) {
            self::OwnerManager => 'Owner / Manager',
            self::CashierAttendant => 'Cashier / Store Attendant',
        };
    }
}
