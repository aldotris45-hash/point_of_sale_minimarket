<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case QRIS = 'qris';
    // payment made today but customer will pay later (piutang / tempo)
    case CASH_TEMPO = 'cash_tempo';
}
