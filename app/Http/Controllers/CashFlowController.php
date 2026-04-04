<?php

namespace App\Http\Controllers;

use App\Enums\CashTransactionCategory;
use App\Enums\RoleStatus;
use App\Enums\TransactionStatus;
use App\Models\CashTransaction;
use App\Models\IncomingGood;
use App\Models\Transaction;
use App\Services\Settings\SettingsServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class CashFlowController extends Controller
{
    public function __construct(
        private readonly SettingsServiceInterface $settings,
    ) {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role !== RoleStatus::ADMIN->value) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
            return $next($request);
        });
    }

    private function getCashFlowData(string $from, string $to): array
    {
        // === Pemasukan (Income) ===
        $incomeSales = (float) Transaction::query()
            ->where('status', TransactionStatus::PAID->value)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('total');

        $incomeAdditional = (float) CashTransaction::query()
            ->where('type', 'in')
            ->whereNotIn('category', ['penjualan', 'pelunasan_tempo'])
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->sum('amount');

        $totalIncome = $incomeSales + $incomeAdditional;

        $totalTransactions = Transaction::query()
            ->where('status', TransactionStatus::PAID->value)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->count();

        // === Pembelian Barang (dari Barang Masuk) ===
        $totalPurchase = (float) IncomingGood::query()
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->sum('total');

        // === Pengeluaran Operasional (dari Buku Kas) ===
        $totalOperational = (float) CashTransaction::query()
            ->where('type', 'out')
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->sum('amount');

        // === Total Pengeluaran = Pembelian + Operasional ===
        $totalExpense = $totalPurchase + $totalOperational;

        // === Laba Bersih ===
        $netBalance = $totalIncome - $totalExpense;

        // === Margin Laba (%) ===
        $marginPercent = $totalIncome > 0 ? (($netBalance / $totalIncome) * 100) : 0;

        // === Detail harian ===
        $dailySales = DB::table('transactions')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COALESCE(SUM(total), 0) as amount'))
            ->where('status', TransactionStatus::PAID->value)
            ->whereNull('deleted_at')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('amount', 'date');

        $dailyIncomes = DB::table('cash_transactions')
            ->select(DB::raw('DATE(date) as dt'), DB::raw('COALESCE(SUM(amount), 0) as amount'))
            ->where('type', 'in')
            ->whereNotIn('category', ['penjualan', 'pelunasan_tempo'])
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->groupBy(DB::raw('DATE(date)'))
            ->pluck('amount', 'dt');

        $dailyPurchase = DB::table('incoming_goods')
            ->select(DB::raw('DATE(date) as dt'), DB::raw('COALESCE(SUM(total), 0) as amount'))
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->groupBy(DB::raw('DATE(date)'))
            ->pluck('amount', 'dt');

        $dailyOperational = DB::table('cash_transactions')
            ->select(DB::raw('DATE(date) as dt'), DB::raw('COALESCE(SUM(amount), 0) as amount'))
            ->where('type', 'out')
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->groupBy(DB::raw('DATE(date)'))
            ->pluck('amount', 'dt');

        // Gabungkan semua tanggal
        $allDates = collect();
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);
        for ($d = (clone $start); $d->lte($end); $d->addDay()) {
            $key = $d->toDateString();
            $inc = (float) ($dailySales[$key] ?? 0) + (float) ($dailyIncomes[$key] ?? 0);
            $pur = (float) ($dailyPurchase[$key] ?? 0);
            $ops = (float) ($dailyOperational[$key] ?? 0);
            $exp = $pur + $ops;
            $allDates->push([
                'date' => $d->format('d/m/Y'),
                'date_raw' => $key,
                'income' => $inc,
                'purchase' => $pur,
                'operational' => $ops,
                'expense' => $exp,
                'balance' => $inc - $exp,
            ]);
        }

        // === Breakdown pengeluaran per kategori ===
        $expenseByCategory = DB::table('cash_transactions')
            ->select('category', DB::raw('COALESCE(SUM(amount), 0) as total'))
            ->where('type', 'out')
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->groupBy('category')
            ->get()
            ->map(function ($row) {
                $enum = CashTransactionCategory::tryFrom($row->category);
                return [
                    'category' => $enum?->label() ?? ucfirst($row->category),
                    'total' => (float) $row->total,
                ];
            });

        // Tambahkan "Pembelian Barang" ke breakdown
        if ($totalPurchase > 0) {
            $expenseByCategory = $expenseByCategory->prepend([
                'category' => 'Pembelian Barang',
                'total' => $totalPurchase,
            ]);
        }

        // === Chart data ===
        $chartLabels = $allDates->pluck('date_raw')->map(fn($d) => Carbon::parse($d)->format('d M'))->toArray();
        $chartIncome = $allDates->pluck('income')->toArray();
        $chartPurchase = $allDates->pluck('purchase')->toArray();
        $chartOperational = $allDates->pluck('operational')->toArray();

        return [
            'from' => $from,
            'to' => $to,
            'incomeSales' => $incomeSales,
            'incomeAdditional' => $incomeAdditional,
            'totalIncome' => $totalIncome,
            'totalTransactions' => $totalTransactions,
            'totalPurchase' => $totalPurchase,
            'totalOperational' => $totalOperational,
            'totalExpense' => $totalExpense,
            'netBalance' => $netBalance,
            'marginPercent' => $marginPercent,
            'dailyData' => $allDates,
            'expenseByCategory' => $expenseByCategory,
            'chartLabels' => $chartLabels,
            'chartIncome' => $chartIncome,
            'chartPurchase' => $chartPurchase,
            'chartOperational' => $chartOperational,
            'currency' => $this->settings->currency(),
        ];
    }

    public function index(Request $request): View
    {
        $from = $request->query('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->query('to', Carbon::now()->toDateString());

        $data = $this->getCashFlowData($from, $to);

        return view('cash_flow.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $from = $request->query('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->query('to', Carbon::now()->toDateString());

        $data = $this->getCashFlowData($from, $to);
        $data['storeName'] = $this->settings->storeName() ?: 'TRIJAYA FRESH';
        $data['storeAddress'] = $this->settings->storeAddress();
        $data['storePhone'] = $this->settings->storePhone();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('cash_flow.pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Laporan_Arus_Kas_{$from}_sampai_{$to}.pdf");
    }

    public function dailyDetail(Request $request)
    {
        $dateRaw = $request->query('date');
        $type = $request->query('type'); // 'income', 'purchase', 'operational'
        
        if (!$dateRaw) {
            abort(400, 'Tanggal diperlukan.');
        }

        $date = Carbon::parse($dateRaw)->format('d F Y');
        $currency = $this->settings->currency();

        // Initialize variables
        $salesItems = collect();
        $otherIncomes = collect();
        $purchases = collect();
        $operationals = collect();

        // Fetch based on requested type
        if ($type === 'income') {
            $salesItems = Transaction::query()
                ->where('status', TransactionStatus::PAID->value)
                ->whereDate('created_at', $dateRaw)
                ->get()
                ->map(fn($t) => [
                    'label' => 'Transaksi',
                    'note' => $t->receipt_no . ($t->customer ? ' - ' . $t->customer->name : ''),
                    'amount' => (float)$t->total,
                    'time' => $t->created_at->format('H:i')
                ]);

            $otherIncomes = CashTransaction::query()
                ->where('type', 'in')
                ->whereNotIn('category', ['penjualan', 'pelunasan_tempo'])
                ->whereDate('date', $dateRaw)
                ->get()
                ->map(fn($c) => [
                    'label' => 'Kas Masuk (' . $c->category . ')',
                    'note' => $c->description ?: '-',
                    'amount' => (float)$c->amount,
                    'time' => Carbon::parse($c->date)->format('H:i')
                ]);
        } elseif ($type === 'purchase') {
            $purchases = IncomingGood::query()
                ->whereDate('date', $dateRaw)
                ->get()
                ->map(fn($p) => [
                    'label' => 'Barang Masuk',
                    'note' => $p->reference_no . ($p->supplier ? ' - ' . $p->supplier->name : ''),
                    'amount' => (float)$p->total,
                    'time' => Carbon::parse($p->date)->format('H:i')
                ]);
        } elseif ($type === 'operational') {
            $operationals = CashTransaction::query()
                ->where('type', 'out')
                ->whereDate('date', $dateRaw)
                ->get()
                ->map(fn($c) => [
                    'label' => 'Pengeluaran (' . $c->category . ')',
                    'note' => $c->description ?: '-',
                    'amount' => (float)$c->amount,
                    'time' => Carbon::parse($c->date)->format('H:i')
                ]);
        }

        return view('cash_flow._detail_modal', compact('date', 'type', 'salesItems', 'otherIncomes', 'purchases', 'operationals', 'currency'));
    }
}
