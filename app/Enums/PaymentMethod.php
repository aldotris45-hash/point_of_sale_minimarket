<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    // payment made today but customer will pay later (piutang / tempo)
    case CASH_TEMPO = 'cash_tempo';
}
