<?php

namespace App\Services\Customer;

use App\Models\Customer;

interface CustomerServiceInterface
{
    public function create(array $data): Customer;

    public function update(Customer $customer, array $data): Customer;

    public function delete(Customer $customer): void;
}
