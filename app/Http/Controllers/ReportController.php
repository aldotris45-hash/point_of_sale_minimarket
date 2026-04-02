<?php

namespace App\Http\Controllers;

use App\Enums\RoleStatus;
use App\Enums\TransactionStatus;
use App\Models\ProductPrice;
use App\Models\Transaction;
use App\Services\Report\ReportServiceInterface;
use App\Services\Settings\SettingsServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
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
            'period' => $request->query('period', 'daily'),
        ];

        $summary = $this->report->summary($filters);
        $topProducts = $this->report->topProducts($filters, 5);
        $slowProducts = $this->report->slowProducts($filters, 5);

        $methods = collect([
            (object) ['value' => 'cash'],
            (object) ['value' => 'cash_tempo'],
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
            'slowProducts' => $slowProducts,
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
            'period' => $request->query('period', 'daily'),
        ];

        $query = ($filters['period'] ?? 'daily') === 'monthly'
            ? $this->report->monthlySalesQuery($filters)
            : $this->report->dailySalesQuery($filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($row) use ($filters) {
                if (($filters['period'] ?? 'daily') === 'monthly') {
                    return $row->date;
                }
                return date('d/m/Y', strtotime($row->date));
            })
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
            'period' => $request->query('period', 'daily'),
        ];

        $periodRows = ($filters['period'] ?? 'daily') === 'monthly'
            ? $this->report->monthlySalesQuery($filters)->get()
            : $this->report->dailySalesQuery($filters)->get();
        $summary = $this->report->summary($filters);
        $top = $this->report->topProducts($filters, 10);
        $slow = $this->report->slowProducts($filters, 10);
        $products = $this->report->productSales($filters);

        $filename = 'laporan-penjualan-' . ($filters['period'] ?? 'daily') . '-' . Str::slug(($filters['from'] ?? 'all') . '_to_' . ($filters['to'] ?? 'all')) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $isMonthly = ($filters['period'] ?? 'daily') === 'monthly';

        $callback = function () use ($periodRows, $summary, $top, $slow, $products, $isMonthly, $filters) {
            $out = fopen('php://output', 'w');
            
            // Bagian 1: Ringkasan
            fputcsv($out, ['Laporan Penjualan']);
            fputcsv($out, ['Periode', $filters['period'] ?? 'daily']);
            fputcsv($out, ['Dari', $filters['from'] ?? '-']);
            fputcsv($out, ['Sampai', $filters['to'] ?? '-']);
            fputcsv($out, ['Status', $filters['status'] ?? '-']);
            fputcsv($out, ['Metode', $filters['method'] ?? '-']);
            fputcsv($out, []);
            fputcsv($out, ['Ringkasan']);
            fputcsv($out, ['Total Penjualan', (int) $summary['total_sales']]);
            fputcsv($out, ['Total Transaksi', (int) $summary['total_transactions']]);
            fputcsv($out, ['Rata-rata per Transaksi', (int) $summary['average_order_value']]);
            fputcsv($out, ['Total Item Terjual', (int) $summary['total_items_sold']]);
            fputcsv($out, []);

            // Bagian 2: Penjualan per Periode (Harian/Bulanan)
            fputcsv($out, ['Penjualan per ' . (($isMonthly) ? 'Bulan' : 'Hari')]);
            fputcsv($out, ['Tanggal', 'Jumlah Transaksi', 'Total Item', 'Total Nominal']);
            foreach ($periodRows as $r) {
                fputcsv($out, [
                    $isMonthly ? $r->date : date('Y-m-d', strtotime($r->date)),
                    (int) $r->trx_count,
                    (int) $r->items_qty,
                    (int) $r->total,
                ]);
            }
            fputcsv($out, []);

            // Bagian 3: Produk Terlaris
            fputcsv($out, ['Top Produk (Terlaris)']);
            fputcsv($out, ['Produk', 'SKU', 'Qty', 'Total']);
            foreach ($products->take(10) as $p) {
                fputcsv($out, [$p->name, $p->sku, (int) $p->qty, (int) $p->total]);
            }
            fputcsv($out, []);

            // Bagian 4: Produk Perputaran Lambat
            fputcsv($out, ['Produk Perputaran Lambat']);
            fputcsv($out, ['Produk', 'Qty', 'Total']);
            foreach ($slow as $p) {
                fputcsv($out, [$p->name, (int) $p->qty, (int) $p->total]);
            }
            fputcsv($out, []);

            // Bagian 5: Detail Penjualan per Produk (lengkap)
            fputcsv($out, ['Detail Penjualan per Produk']);
            fputcsv($out, ['Produk', 'SKU', 'Qty', 'Total']);
            foreach ($products as $p) {
                fputcsv($out, [$p->name, $p->sku, (int) $p->qty, (int) $p->total]);
            }
            fclose($out);
        };

        return response()->stream($callback, Response::HTTP_OK, $headers);
    }

    /**
     * Print-friendly view of detailed transactions for PDF export via browser.
     */
    public function printTransactions(Request $request): View
    {
        $from   = $request->query('from');
        $to     = $request->query('to');
        $status = $request->query('status', 'paid');
        $method = $request->query('method');
        $period = $request->query('period', 'daily');

        // Build query for transactions with details
        $query = Transaction::query()
            ->with(['details.product', 'user', 'customer'])
            ->where('status', '!=', 'suspended')
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($method, fn($q) => $q->where('payment_method', $method))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderBy('created_at', 'asc');

        $transactions = $query->get();

        // Build cost price cache (latest cost per product)
        $costPriceCache = [];
        $productIds = $transactions->pluck('details')->flatten()->pluck('product_id')->unique();
        if ($productIds->isNotEmpty()) {
            $latestPrices = ProductPrice::query()
                ->whereIn('product_id', $productIds)
                ->orderByDesc('price_date')
                ->get()
                ->unique('product_id')
                ->keyBy('product_id');
            foreach ($latestPrices as $pid => $pp) {
                $costPriceCache[$pid] = (float) $pp->cost_price;
            }
        }

        // Build flat records (one row per item)
        $records = collect();
        $totals = ['cost' => 0, 'selling' => 0, 'qty' => 0, 'amount' => 0, 'kas_masuk' => 0, 'profit' => 0];

        foreach ($transactions as $trx) {
            $pm = is_string($trx->payment_method) ? $trx->payment_method : ($trx->payment_method?->value ?? '');
            $pmLabel = $pm === 'cash_tempo' ? 'Tunai Tempo' : ucfirst($pm);

            foreach ($trx->details as $detail) {
                $costPrice = $costPriceCache[$detail->product_id] ?? 0;
                $sellingPrice = (float) $detail->price;
                $qty = (int) $detail->quantity;
                $amount = (float) $detail->total;
                $kasMasuk = $amount; // kas masuk = jumlah penjualan
                $profit = $amount - ($costPrice * $qty);

                $row = [
                    'invoice_number' => $trx->invoice_number,
                    'date'           => $trx->created_at->format('d/M/Y'),
                    'cashier'        => $trx->user->name ?? '-',
                    'customer'       => $trx->customer->name ?? '',
                    'payment_method' => $pmLabel,
                    'product_name'   => $detail->product->name ?? '-',
                    'cost_price'     => $costPrice,
                    'selling_price'  => $sellingPrice,
                    'qty'            => $qty,
                    'unit'           => $detail->product->unit ?? 'Pcs',
                    'amount'         => $amount,
                    'kas_masuk'      => $kasMasuk,
                    'profit'         => $profit,
                ];

                $records->push($row);

                $totals['cost']      += $costPrice * $qty;
                $totals['selling']   += $sellingPrice * $qty;
                $totals['qty']       += $qty;
                $totals['amount']    += $amount;
                $totals['kas_masuk'] += $kasMasuk;
                $totals['profit']    += $profit;
            }
        }

        // Period label
        $periodLabel = $period === 'monthly' ? 'Bulanan' : 'Harian';
        if ($from && $to) {
            $periodLabel .= " ({$from} s/d {$to})";
        } elseif ($from) {
            $periodLabel .= " (dari {$from})";
        } elseif ($to) {
            $periodLabel .= " (s/d {$to})";
        }

        return view('reports.print-transaksi', [
            'records'      => $records,
            'totals'       => $totals,
            'period_label' => $periodLabel,
            'from'         => $from,
            'to'           => $to,
            'status'       => $status,
            'method'       => $method,
            'store_name'   => $this->settings->storeName(),
            'store_address' => $this->settings->storeAddress(),
            'store_phone'  => $this->settings->storePhone(),
            'store_logo'   => $this->settings->storeLogoPath(),
        ]);
    }
}
