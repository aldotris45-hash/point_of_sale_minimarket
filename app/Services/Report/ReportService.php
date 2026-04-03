<?php

namespace App\Services\Report;

use App\Models\ProductPrice;
use App\Models\Transaction;
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
            ->whereNull('t.deleted_at')
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
        $dateExpr = DB::raw('DATE(t.created_at)');

        $trx = $this->baseTransactionQuery($filters)
            ->selectRaw('DATE(t.created_at) as grp_date, COUNT(*) as trx_count, COALESCE(SUM(t.total),0) as total')
            ->groupBy($dateExpr);

        $items = DB::table('transaction_details as d')
            ->join('transactions as t', 't.id', '=', 'd.transaction_id')
            ->whereNull('t.deleted_at')
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '<=', $v))
            ->when(!empty($filters['status']), fn($q) => $q->where('t.status', $filters['status']))
            ->when(!empty($filters['method']), fn($q) => $q->where('t.payment_method', $filters['method']))
            ->selectRaw('DATE(t.created_at) as grp_date, COALESCE(SUM(d.quantity),0) as items_qty')
            ->groupBy($dateExpr);

        return DB::query()
            ->fromSub($trx, 'x')
            ->leftJoinSub($items, 'i', 'i.grp_date', '=', 'x.grp_date')
            ->selectRaw('x.grp_date as date, x.trx_count, COALESCE(i.items_qty,0) as items_qty, x.total')
            ->orderBy('x.grp_date');
    }

    public function monthlySalesQuery(array $filters): Builder
    {
        $driver = DB::connection()->getDriverName();
        $expr = $driver === 'sqlite'
            ? "strftime('%Y-%m', t.created_at)"
            : "DATE_FORMAT(t.created_at, '%Y-%m')";

        $trx = $this->baseTransactionQuery($filters)
            ->selectRaw("$expr as grp_date, COUNT(*) as trx_count, COALESCE(SUM(t.total),0) as total")
            ->groupBy(DB::raw($expr));

        $items = DB::table('transaction_details as d')
            ->join('transactions as t', 't.id', '=', 'd.transaction_id')
            ->whereNull('t.deleted_at')
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '<=', $v))
            ->when(!empty($filters['status']), fn($q) => $q->where('t.status', $filters['status']))
            ->when(!empty($filters['method']), fn($q) => $q->where('t.payment_method', $filters['method']))
            ->selectRaw("$expr as grp_date, COALESCE(SUM(d.quantity),0) as items_qty")
            ->groupBy(DB::raw($expr));

        return DB::query()
            ->fromSub($trx, 'x')
            ->leftJoinSub($items, 'i', 'i.grp_date', '=', 'x.grp_date')
            ->selectRaw('x.grp_date as date, x.trx_count, COALESCE(i.items_qty,0) as items_qty, x.total')
            ->orderBy('x.grp_date');
    }

    public function topProducts(array $filters, int $limit = 5): Collection
    {
        return DB::table('transaction_details as d')
            ->join('transactions as t', 't.id', '=', 'd.transaction_id')
            ->whereNull('t.deleted_at')
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

    public function slowProducts(array $filters, int $limit = 5): Collection
    {
        $sold = DB::table('transaction_details as d')
            ->join('transactions as t', 't.id', '=', 'd.transaction_id')
            ->whereNull('t.deleted_at')
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '<=', $v))
            ->when(!empty($filters['status']), fn($q) => $q->where('t.status', $filters['status']))
            ->when(!empty($filters['method']), fn($q) => $q->where('t.payment_method', $filters['method']))
            ->groupBy('d.product_id')
            ->selectRaw('d.product_id, SUM(d.quantity) as qty, SUM(d.total) as total');

        return DB::table('products as p')
            ->leftJoinSub($sold, 's', 's.product_id', '=', 'p.id')
            ->orderByRaw('COALESCE(s.qty, 0) ASC, p.stock DESC, p.name ASC')
            ->limit($limit)
            ->selectRaw('p.id as product_id, p.name, COALESCE(s.qty, 0) as qty, COALESCE(s.total, 0) as total, p.stock')
            ->get();
    }

    public function productSales(array $filters): Collection
    {
        return DB::table('transaction_details as d')
            ->join('transactions as t', 't.id', '=', 'd.transaction_id')
            ->whereNull('t.deleted_at')
            ->leftJoin('products as p', 'p.id', '=', 'd.product_id')
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '<=', $v))
            ->when(!empty($filters['status']), fn($q) => $q->where('t.status', $filters['status']))
            ->when(!empty($filters['method']), fn($q) => $q->where('t.payment_method', $filters['method']))
            ->groupBy('d.product_id', 'p.name', 'p.sku')
            ->orderByDesc(DB::raw('SUM(d.quantity)'))
            ->selectRaw('d.product_id, COALESCE(p.name, CONCAT("#", d.product_id)) as name, p.sku, SUM(d.quantity) as qty, SUM(d.total) as total')
            ->get();
    }

    private function baseTransactionQuery(array $filters)
    {
        return DB::table('transactions as t')
            ->whereNull('t.deleted_at')
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn($q, $v) => $q->whereDate('t.created_at', '<=', $v))
            ->when(!empty($filters['status']), fn($q) => $q->where('t.status', $filters['status']))
            ->when(!empty($filters['method']), fn($q) => $q->where('t.payment_method', $filters['method']));
    }

    /**
     * Build detailed per-item transaction records with cost/profit calculations.
     *
     * @param  array  $filters  [from, to, status, method, period]
     * @return array{records: Collection, totals: array}
     */
    public function printTransactionsData(array $filters): array
    {
        $from   = $filters['from'] ?? null;
        $to     = $filters['to'] ?? null;
        $status = $filters['status'] ?? 'paid';
        $method = $filters['method'] ?? null;

        $transactions = Transaction::query()
            ->with(['details.product', 'user', 'customer'])
            ->where('status', '!=', 'suspended')
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($method, fn($q) => $q->where('payment_method', $method))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderBy('created_at', 'asc')
            ->get();

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
                $costPrice    = $costPriceCache[$detail->product_id] ?? 0;
                $sellingPrice = (float) $detail->price;
                $qty          = (int) $detail->quantity;
                $amount       = (float) $detail->total;
                $kasMasuk     = $amount;
                $profit       = $amount - ($costPrice * $qty);

                $records->push([
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
                ]);

                $totals['cost']      += $costPrice * $qty;
                $totals['selling']   += $sellingPrice * $qty;
                $totals['qty']       += $qty;
                $totals['amount']    += $amount;
                $totals['kas_masuk'] += $kasMasuk;
                $totals['profit']    += $profit;
            }
        }

        return [
            'records' => $records,
            'totals'  => $totals,
        ];
    }
}
