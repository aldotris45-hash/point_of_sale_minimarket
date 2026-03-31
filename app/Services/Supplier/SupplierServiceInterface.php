<?php

namespace App\Services\Supplier;

use App\Models\Supplier;

interface SupplierServiceInterface
{
    public function create(array $data): Supplier;

    public function update(Supplier $supplier, array $data): Supplier;

    public function delete(Supplier $supplier): void;
}
