<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\Report\ReportServiceInterface;
use App\Services\Settings\SettingsServiceInterface;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ReportServiceInterface $report,
        private readonly SettingsServiceInterface $settings,
    ) {}

    public function index(): View
    {
        $today = Carbon::today();
        $from7 = (clone $today)->subDays(6);

        // KPI cards
        $salesToday = (float) Transaction::query()
            ->whereDate('created_at', $today)
            ->where('status', 'paid')
            ->sum('total');

        $trxToday = (int) Transaction::query()
            ->whereDate('created_at', $today)
            ->count('id');

        $outOfStock = (int) Product::query()->where('stock', '<=', 0)->count('id');
        $lowStock = (int) Product::query()->where('stock', '>', 0)->where('stock', '<=', 5)->count('id');

        // 7-day sales chart (paid only)
        $rows = $this->report->dailySalesQuery([
            'from' => $from7->toDateString(),
            'to' => $today->toDateString(),
            'status' => 'paid',
        ])->get();
        $byDate = collect($rows)->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());
        $dates = [];
        $values = [];
        for ($d = (clone $from7); $d->lte($today); $d->addDay()) {
            $key = $d->toDateString();
            $dates[] = $d->format('d M');
            $values[] = (float) ($byDate[$key]->total ?? 0.0);
        }
        $max = max(1.0, max($values));

        // Top products today (by qty)
        $topToday = DB::table('transaction_details as d')
            ->join('transactions as t', 't.id', '=', 'd.transaction_id')
            ->leftJoin('products as p', 'p.id', '=', 'd.product_id')
            ->whereDate('t.created_at', $today->toDateString())
            ->groupBy('d.product_id', 'p.name')
            ->orderByDesc(DB::raw('SUM(d.quantity)'))
            ->limit(5)
            ->selectRaw('COALESCE(p.name, CONCAT("#", d.product_id)) as name, SUM(d.quantity) as qty, SUM(d.total) as total')
            ->get();

        return view('home', [
            'currency' => $this->settings->currency(),
            'salesToday' => $salesToday,
            'trxToday' => $trxToday,
            'outOfStock' => $outOfStock,
            'lowStock' => $lowStock,
            'chartLabels' => $dates,
            'chartValues' => $values,
            'chartMax' => $max,
            'topToday' => $topToday,
        ]);
    }
}
