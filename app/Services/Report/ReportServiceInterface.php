<?php

namespace App\Services\Report;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

interface ReportServiceInterface
{
    public function summary(array $filters): array;

    public function dailySalesQuery(array $filters): Builder;

    public function topProducts(array $filters, int $limit = 5): Collection;

    public function monthlySalesQuery(array $filters): Builder;

    public function slowProducts(array $filters, int $limit = 5): Collection;

    public function productSales(array $filters): Collection;

    /**
     * Build detailed per-item transaction records with cost/profit calculations.
     *
     * @param  array  $filters  [from, to, status, method, period]
     * @return array{records: Collection, totals: array}
     */
    public function printTransactionsData(array $filters): array;
}
