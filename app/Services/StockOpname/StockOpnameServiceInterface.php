<?php

namespace App\Services\StockOpname;

use App\Models\StockOpname;

interface StockOpnameServiceInterface
{
    public function create(array $data): StockOpname;

    public function delete(StockOpname $stockOpname): void;
}
