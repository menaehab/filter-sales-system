<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Models\Customer;

final class CreateCustomerAction
{
    public function execute(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'national_number' => $data['national_number'] ?? null,
            'address' => $data['address'] ?? null,
        ]);
    }
}
