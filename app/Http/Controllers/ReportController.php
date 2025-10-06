<?php

namespace App\Http\Controllers;

use App\Enums\RoleStatus;
use App\Services\Report\ReportServiceInterface;
use App\Services\Settings\SettingsServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportServiceInterface $report,
        private readonly SettingsServiceInterface $settings,
    ) {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== RoleStatus::ADMIN->value) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $filters = [
            'from'   => $request->query('from'),
            'to'     => $request->query('to'),
            'status' => $request->query('status', 'paid'),
            'method' => $request->query('method'),
        ];

        $summary = $this->report->summary($filters);
        $topProducts = $this->report->topProducts($filters, 5);

        $methods = collect([
            (object) ['value' => 'cash'],
            (object) ['value' => 'qris'],
        ]);
        $statuses = collect([
            (object) ['value' => 'paid'],
            (object) ['value' => 'pending'],
            (object) ['value' => 'void'],
        ]);

        return view('reports.index', [
            'filters'     => $filters,
            'summary'     => $summary,
            'topProducts' => $topProducts,
            'methods'     => $methods,
            'statuses'    => $statuses,
            'currency'    => $this->settings->currency(),
        ]);
    }

    public function data(Request $request)
    {
        $filters = [
            'from'   => $request->query('from'),
            'to'     => $request->query('to'),
            'status' => $request->query('status', 'paid'),
            'method' => $request->query('method'),
        ];

        $query = $this->report->dailySalesQuery($filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', fn($row) => date('d/m/Y', strtotime($row->date)))
            ->editColumn('total', fn($row) => 'Rp ' . number_format((float) $row->total, 0, ',', '.'))
            ->toJson();
    }

    public function download(Request $request)
    {
        $filters = [
            'from'   => $request->query('from'),
            'to'     => $request->query('to'),
            'status' => $request->query('status', 'paid'),
            'method' => $request->query('method'),
        ];

        $rows = $this->report->dailySalesQuery($filters)->get();

        $filename = 'laporan-penjualan-' . Str::slug(($filters['from'] ?? 'all') . '_to_' . ($filters['to'] ?? 'all')) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Jumlah Transaksi', 'Total Item', 'Total Nominal']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    date('Y-m-d', strtotime($r->date)),
                    (int) $r->trx_count,
                    (int) $r->items_qty,
                    (int) $r->total,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, Response::HTTP_OK, $headers);
    }
}
