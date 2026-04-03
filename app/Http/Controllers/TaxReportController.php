<?php

namespace App\Http\Controllers;

use App\Enums\RoleStatus;
use App\Services\Settings\SettingsServiceInterface;
use App\Services\Tax\TaxReportServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaxReportController extends Controller
{
    public function __construct(
        private readonly TaxReportServiceInterface $taxReport,
        private readonly SettingsServiceInterface $settings,
    ) {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== RoleStatus::ADMIN->value) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
            return $next($request);
        });
    }

    /**
     * Halaman utama Laporan Pajak.
     */
    public function index(Request $request): View
    {
        $year = (int) $request->query('year', now()->year);
        $data = $this->taxReport->yearlyTaxSummary($year);

        return view('tax.index', array_merge($data, [
            'currency' => $this->settings->currency(),
        ]));
    }

    /**
     * Export PDF rekap pajak tahunan.
     */
    public function exportPdf(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $data = $this->taxReport->yearlyTaxSummary($year);

        $data['store_name']    = $this->settings->storeName() ?: 'Toko';
        $data['store_address'] = $this->settings->storeAddress();
        $data['store_phone']   = $this->settings->storePhone();
        $data['currency']      = $this->settings->currency();

        $pdf = Pdf::loadView('tax.pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Rekap-Pajak-UMKM-{$year}.pdf");
    }

    /**
     * Export CSV rekap pajak (siap untuk e-Filing / Excel).
     */
    public function exportCsv(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $data = $this->taxReport->yearlyTaxSummary($year);

        $filename = "Rekap-Pajak-UMKM-{$year}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($data, $year) {
            $out = fopen('php://output', 'w');

            // Header info
            fputcsv($out, ["Rekap Pajak UMKM Tahun {$year}"]);
            fputcsv($out, ['Tarif PPh Final', $data['summary']['tax_rate_percent'] . '%']);
            fputcsv($out, ['Batas PTKP', 'Rp ' . number_format($data['summary']['ptkp_limit'], 0, ',', '.')]);
            fputcsv($out, []);

            // Tabel bulanan
            fputcsv($out, ['Bulan', 'Jml Transaksi', 'Omset Bruto', 'Omset Kumulatif', 'Status PTKP', 'Omset Kena Pajak', 'PPh Final 0.5%']);

            foreach ($data['months'] as $m) {
                fputcsv($out, [
                    $m['month'],
                    $m['trx_count'],
                    $m['gross_revenue'],
                    $m['cumulative'],
                    $m['is_below_ptkp'] ? 'Di bawah PTKP' : 'Kena Pajak',
                    $m['taxable_revenue'],
                    $m['tax'],
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['TOTAL', '', $data['summary']['total_revenue'], '', '', $data['summary']['total_taxable_revenue'], $data['summary']['total_tax']]);

            fclose($out);
        };

        return response()->stream($callback, Response::HTTP_OK, $headers);
    }
}
