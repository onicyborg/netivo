<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case TRANSFER = 'transfer';
    case EWALLET = 'ewallet';
}
