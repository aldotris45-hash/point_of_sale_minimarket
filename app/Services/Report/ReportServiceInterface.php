<?php

namespace App\Services\Report;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

interface ReportServiceInterface
{
    public function summary(array $filters): array;

    public function dailySalesQuery(array $filters): Builder;

    public function topProducts(array $filters, int $limit = 5): Collection;
}
