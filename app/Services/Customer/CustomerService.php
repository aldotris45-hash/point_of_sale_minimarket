<?php

namespace App\Services\Customer;

use App\Models\Customer;

class CustomerService implements CustomerServiceInterface
{
    public function create(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $customer;
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
