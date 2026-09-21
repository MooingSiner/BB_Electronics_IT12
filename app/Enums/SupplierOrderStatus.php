<?php

namespace App\Enums;

enum SupplierOrderStatus: string
{
    case Pending = 'pending';
    case Received = 'received';
    case Cancelled = 'cancelled';
}
