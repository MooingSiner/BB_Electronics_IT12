<?php

namespace App\Enums;

enum SalesTransactionStatus: string
{
    case Completed = 'completed';
    case Voided = 'voided';
}
