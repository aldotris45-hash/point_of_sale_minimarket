<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case GAJI = 'gaji';
    case LISTRIK = 'listrik';
    case BENSIN = 'bensin';
    case SUPPLIES = 'supplies';
    case LAINNYA = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::GAJI => 'Gaji',
            self::LISTRIK => 'Listrik',
            self::BENSIN => 'Bensin',
            self::SUPPLIES => 'Supplies/Perlengkapan',
            self::LAINNYA => 'Lainnya',
        };
    }
}
