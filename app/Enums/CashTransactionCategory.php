<?php

namespace App\Enums;

enum CashTransactionCategory: string
{
    // Pemasukan
    case PENJUALAN = 'penjualan';
    case PELUNASAN_TEMPO = 'pelunasan_tempo';
    case TAMBAHAN_MODAL = 'tambahan_modal';
    case PENDAPATAN_LAIN = 'pendapatan_lain';

    // Pengeluaran
    case GAJI = 'gaji';
    case LISTRIK = 'listrik';
    case OPERASIONAL = 'operasional';
    case LAINNYA = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::PENJUALAN => 'Penjualan',
            self::PELUNASAN_TEMPO => 'Pelunasan Tempo',
            self::TAMBAHAN_MODAL => 'Tambahan Modal',
            self::PENDAPATAN_LAIN => 'Pendapatan Lain',
            self::GAJI => 'Gaji Karyawan',
            self::LISTRIK => 'Listrik / Air / Internet',
            self::OPERASIONAL => 'Operasional / Bensin',
            self::LAINNYA => 'Lainnya',
        };
    }
}
