<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING  = 'pending';
    case PAID     = 'paid';
    case CANCELED = 'canceled';
    case REFUNDED = 'refunded';
}
