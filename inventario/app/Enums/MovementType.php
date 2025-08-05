<?php

namespace App\Enums;

enum MovementType: string
{
    case IN = 'in';
    case OUT = 'out';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case ADJUSTMENT = 'adjustment';
    case ADJUSTMENT_POS = 'adjustment_pos';
    case ADJUSTMENT_NEG = 'adjustment_neg';

}
