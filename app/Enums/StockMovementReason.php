<?php

namespace App\Enums;

enum StockMovementReason: string
{
    case SupplierDelivery = 'supplier_delivery';
    case CustomerSale = 'customer_sale';
    case CustomerReturn = 'customer_return';
    case ServiceUse = 'service_use';
    case Adjustment = 'adjustment';
}
