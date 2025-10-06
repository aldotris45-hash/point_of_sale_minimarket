<?php

namespace App\Services\Report;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService implements ReportServiceInterface
{
    public function summary(array $filters): array
    {
        $trx = $this->baseTransactionQuery($filters);
        $sum = (clone $trx)->selectRaw('COUNT(*) as trx_count, COALESCE(SUM(t.total),0) as total')->first();

        $items = DB::table('transaction_details as d')
            ->join('transactions as t', 't.id', '=', 'd.transaction_id')
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '<=', $v))
            ->when(!empty($filters['status']), fn($q) => $q->where('t.status', $filters['status']))
            ->when(!empty($filters['method']), fn($q) => $q->where('t.payment_method', $filters['method']))
            ->selectRaw('COALESCE(SUM(d.quantity),0) as items_qty')
            ->first();

        $totalSales = (float) ($sum->total ?? 0);
        $totalTrx = (int) ($sum->trx_count ?? 0);
        $avg = $totalTrx > 0 ? ($totalSales / $totalTrx) : 0.0;

        return [
            'total_sales' => $totalSales,
            'total_transactions' => $totalTrx,
            'average_order_value' => $avg,
            'total_items_sold' => (int) ($items->items_qty ?? 0),
        ];
    }

    public function dailySalesQuery(array $filters): Builder
    {
        $q = DB::table('transactions as t')
            ->leftJoin('transaction_details as d', 'd.transaction_id', '=', 't.id')
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '<=', $v))
            ->when(!empty($filters['status']), fn($q) => $q->where('t.status', $filters['status']))
            ->when(!empty($filters['method']), fn($q) => $q->where('t.payment_method', $filters['method']))
            ->groupBy(DB::raw('DATE(t.created_at)'))
            ->orderBy(DB::raw('DATE(t.created_at)'))
            ->selectRaw('DATE(t.created_at) as date, COUNT(DISTINCT t.id) as trx_count, COALESCE(SUM(d.quantity),0) as items_qty, COALESCE(SUM(t.total),0) as total');

        return $q;
    }

    public function topProducts(array $filters, int $limit = 5): Collection
    {
        return DB::table('transaction_details as d')
            ->join('transactions as t', 't.id', '=', 'd.transaction_id')
            ->leftJoin('products as p', 'p.id', '=', 'd.product_id')
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '<=', $v))
            ->when(!empty($filters['status']), fn($q) => $q->where('t.status', $filters['status']))
            ->when(!empty($filters['method']), fn($q) => $q->where('t.payment_method', $filters['method']))
            ->groupBy('d.product_id', 'p.name')
            ->orderByDesc(DB::raw('SUM(d.quantity)'))
            ->limit($limit)
            ->selectRaw('d.product_id, COALESCE(p.name, CONCAT("#", d.product_id)) as name, SUM(d.quantity) as qty, SUM(d.total) as total')
            ->get();
    }

    private function baseTransactionQuery(array $filters)
    {
        return DB::table('transactions as t')
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '<=', $v))
            ->when(!empty($filters['status']), fn($q) => $q->where('t.status', $filters['status']))
            ->when(!empty($filters['method']), fn($q) => $q->where('t.payment_method', $filters['method']));
    }
}
