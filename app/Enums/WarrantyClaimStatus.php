<?php

namespace App\Enums;

enum WarrantyClaimStatus: string
{
    case None = 'none';
    case Claimed = 'claimed';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
}
