<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH_CUP = 'cash_cup';
    case CASH_USD = 'cash_usd';
    case TRANSFER_CUP = 'transfer_cup';
    case TRANSFER_MLC = 'transfer_mlc';
    case TRANSFER_USD = 'transfer_usd';
}
