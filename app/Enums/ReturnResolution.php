<?php

namespace App\Enums;

enum ReturnResolution: string
{
    case Refund = 'refund';
    case Replacement = 'replacement';
    case Warranty = 'warranty';
}
