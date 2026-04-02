<?php

namespace App\Http\Controllers;

use App\Enums\ExpenseCategory;
use App\Enums\RoleStatus;
use App\Enums\TransactionStatus;
use App\Models\Expense;
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

        // === Pemasukan (Income) dari transaksi PAID ===
        $incomeQuery = Transaction::query()
            ->where('status', TransactionStatus::PAID->value)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        $totalIncome = (float) (clone $incomeQuery)->sum('total');
        $totalTransactions = (int) (clone $incomeQuery)->count();

        // === Pengeluaran (Expenses) ===
        $expenseQuery = Expense::query()
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to);

        $totalExpense = (float) (clone $expenseQuery)->sum('amount');

        // === Saldo Bersih ===
        $netBalance = $totalIncome - $totalExpense;

        // === Detail harian ===
        $dailyIncome = DB::table('transactions')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COALESCE(SUM(total), 0) as amount'))
            ->where('status', TransactionStatus::PAID->value)
            ->whereNull('deleted_at')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('amount', 'date');

        $dailyExpense = DB::table('expenses')
            ->select(DB::raw('DATE(expense_date) as date'), DB::raw('COALESCE(SUM(amount), 0) as amount'))
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->groupBy(DB::raw('DATE(expense_date)'))
            ->pluck('amount', 'date');

        // Gabungkan semua tanggal
        $allDates = collect();
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);
        for ($d = (clone $start); $d->lte($end); $d->addDay()) {
            $key = $d->toDateString();
            $inc = (float) ($dailyIncome[$key] ?? 0);
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
        $expenseByCategory = DB::table('expenses')
            ->select('category', DB::raw('COALESCE(SUM(amount), 0) as total'))
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->groupBy('category')
            ->get()
            ->map(function ($row) {
                $enum = ExpenseCategory::tryFrom($row->category);
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
