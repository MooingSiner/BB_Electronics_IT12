<?php

namespace App\Enums;

enum WarrantyOutcome: string
{
    case Replacement = 'replacement';
    case Refund = 'refund';
    case Repair = 'repair';
    case SupplierExchange = 'supplier_exchange';
    case Denied = 'denied';
    case NotApplicable = 'n_a';
}
