<?php

namespace App\Enums;

enum ReturnCondition: string
{
    case Defective = 'defective';
    case Damaged = 'damaged';
    case WrongItem = 'wrong_item';
    case CustomerChangedMind = 'customer_changed_mind';
    case Other = 'other';
}
