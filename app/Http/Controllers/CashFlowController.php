<?php

namespace App\Http\Controllers;

use App\Enums\CashTransactionCategory;
use App\Enums\RoleStatus;
use App\Enums\TransactionStatus;
use App\Models\CashTransaction;
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

    public function index(Request $request): View
    {
        $from = $request->query('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->query('to', Carbon::now()->toDateString());

        // === Pemasukan (Income) dari transaksi PAID + CashTransaction type 'in' ===
        $incomeSales = Transaction::query()
            ->where('status', TransactionStatus::PAID->value)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('total');
            
        $incomeAdditional = CashTransaction::query()
            ->where('type', 'in')
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->sum('amount');

        $totalIncome = (float) $incomeSales + (float) $incomeAdditional;
        $totalTransactions = Transaction::query()
            ->where('status', TransactionStatus::PAID->value)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->count();

        // === Pengeluaran (Expenses) ===
        $totalExpense = (float) CashTransaction::query()
            ->where('type', 'out')
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->sum('amount');

        // === Saldo Bersih ===
        $netBalance = $totalIncome - $totalExpense;

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
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->groupBy(DB::raw('DATE(date)'))
            ->pluck('amount', 'dt');

        $dailyExpense = DB::table('cash_transactions')
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
            $exp = (float) ($dailyExpense[$key] ?? 0);
            $allDates->push([
                'date' => $d->format('d/m/Y'),
                'date_raw' => $key,
                'income' => $inc,
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

        // === Chart data ===
        $chartLabels = $allDates->pluck('date_raw')->map(fn($d) => Carbon::parse($d)->format('d M'))->toArray();
        $chartIncome = $allDates->pluck('income')->toArray();
        $chartExpense = $allDates->pluck('expense')->toArray();

        return view('cash_flow.index', [
            'from' => $from,
            'to' => $to,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'totalTransactions' => $totalTransactions,
            'netBalance' => $netBalance,
            'dailyData' => $allDates,
            'expenseByCategory' => $expenseByCategory,
            'chartLabels' => $chartLabels,
            'chartIncome' => $chartIncome,
            'chartExpense' => $chartExpense,
            'currency' => $this->settings->currency(),
        ]);
    }
}

