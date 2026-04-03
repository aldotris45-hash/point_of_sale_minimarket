<?php

namespace App\Services\Tax;

interface TaxReportServiceInterface
{
    /**
     * Get monthly gross revenue, PPh Final 0.5%, and cumulative totals for a given year.
     *
     * Each month entry contains:
     * - month (string): Nama bulan
     * - gross_revenue (float): Omset bruto bulan tersebut
     * - cumulative (float): Omset kumulatif dari Januari s/d bulan tersebut
     * - is_below_ptkp (bool): Apakah omset kumulatif masih di bawah PTKP (Rp 500jt)
     * - taxable_revenue (float): Bagian omset yang kena pajak
     * - tax (float): PPh Final 0.5% yang harus disetor
     *
     * @param  int  $year
     * @return array{months: array, summary: array, available_years: array}
     */
    public function yearlyTaxSummary(int $year): array;
}
