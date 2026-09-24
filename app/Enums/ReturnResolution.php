<?php

namespace App\Enums;

enum ReturnResolution: string
{
    case Replacement = 'replacement';
    case Refund = 'refund';
    case Repair = 'repair';
    case SupplierExchange = 'supplier_exchange';
    case Pending = 'pending';
}
