<?php

namespace App\Services\Tax;

use App\Enums\TransactionStatus;
use Illuminate\Support\Facades\DB;

class TaxReportService implements TaxReportServiceInterface
{
    /**
     * Tarif PPh Final UMKM (PP 55/2022).
     */
    private const TAX_RATE = 0.005; // 0.5%

    /**
     * Batas PTKP (Penghasilan Tidak Kena Pajak) per tahun.
     * Omset kumulatif di bawah ini = tidak kena pajak.
     * Berlaku sejak PP 55/2022.
     */
    private const PTKP_LIMIT = 500_000_000; // Rp 500.000.000

    /**
     * Nama-nama bulan dalam Bahasa Indonesia.
     */
    private const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
        4 => 'April',   5 => 'Mei',      6 => 'Juni',
        7 => 'Juli',    8 => 'Agustus',  9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /**
     * {@inheritDoc}
     */
    public function yearlyTaxSummary(int $year): array
    {
        // Query omset + jumlah transaksi per bulan (satu query, tidak duplikat)
        $monthlyData = DB::table('transactions')
            ->select(
                DB::raw('MONTH(created_at) as month_num'),
                DB::raw('COALESCE(SUM(total), 0) as gross_revenue'),
                DB::raw('COUNT(*) as trx_count')
            )
            ->where('status', TransactionStatus::PAID->value)
            ->whereNull('deleted_at')
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get()
            ->keyBy('month_num');

        // Build 12 bulan
        $months = [];
        $cumulative = 0;
        $totalTax = 0;
        $totalRevenue = 0;
        $totalTaxableRevenue = 0;

        for ($m = 1; $m <= 12; $m++) {
            $row = $monthlyData->get($m);
            $revenue = $row ? (float) $row->gross_revenue : 0.0;
            $trxCount = $row ? (int) $row->trx_count : 0;
            $cumulative += $revenue;

            // PTKP logic: omset kumulatif di bawah 500jt = tidak kena pajak
            $isBelowPtkp = $cumulative <= self::PTKP_LIMIT;

            // Hitung bagian omset yang kena pajak bulan ini
            if ($isBelowPtkp) {
                // Seluruh omset bulan ini masih di bawah PTKP → pajak = 0
                $taxableRevenue = 0;
            } else {
                // Omset kumulatif sudah melewati PTKP
                $previousCumulative = $cumulative - $revenue;
                if ($previousCumulative >= self::PTKP_LIMIT) {
                    // Bulan sebelumnya sudah lewat PTKP → seluruh omset bulan ini kena pajak
                    $taxableRevenue = $revenue;
                } else {
                    // Bulan ini yang melewati batas → hanya bagian di atas 500jt yang kena
                    $taxableRevenue = $cumulative - self::PTKP_LIMIT;
                }
            }

            $tax = $taxableRevenue * self::TAX_RATE;

            $months[] = [
                'month_num'        => $m,
                'month'            => self::MONTH_NAMES[$m],
                'gross_revenue'    => $revenue,
                'trx_count'        => $trxCount,
                'cumulative'       => $cumulative,
                'is_below_ptkp'    => $isBelowPtkp,
                'taxable_revenue'  => $taxableRevenue,
                'tax'              => $tax,
            ];

            $totalRevenue += $revenue;
            $totalTaxableRevenue += $taxableRevenue;
            $totalTax += $tax;
        }

        // Tahun-tahun yang tersedia (dari transaksi paling awal)
        $availableYears = $this->getAvailableYears();

        return [
            'year'   => $year,
            'months' => $months,
            'summary' => [
                'total_revenue'         => $totalRevenue,
                'total_taxable_revenue' => $totalTaxableRevenue,
                'total_tax'             => $totalTax,
                'ptkp_limit'            => self::PTKP_LIMIT,
                'tax_rate'              => self::TAX_RATE,
                'tax_rate_percent'      => self::TAX_RATE * 100,
                'is_below_ptkp'         => $totalRevenue <= self::PTKP_LIMIT,
                'remaining_ptkp'        => max(0, self::PTKP_LIMIT - $totalRevenue),
            ],
            'available_years' => $availableYears,
        ];
    }

    /**
     * Detect tahun-tahun yang memiliki transaksi.
     *
     * @return array<int>
     */
    private function getAvailableYears(): array
    {
        $years = DB::table('transactions')
            ->select(DB::raw('DISTINCT YEAR(created_at) as yr'))
            ->whereNull('deleted_at')
            ->orderByDesc('yr')
            ->pluck('yr')
            ->map(fn ($y) => (int) $y)
            ->toArray();

        // Pastikan tahun berjalan selalu ada
        $currentYear = (int) now()->year;
        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        return $years;
    }
}
