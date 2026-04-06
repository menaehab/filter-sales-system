<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Models\Customer;

final class UpdateCustomerAction
{
    public function execute(Customer $customer, array $data): Customer
    {
        $customer->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'code' => $data['code'],
            'national_number' => $data['national_number'] ?? null,
            'address' => $data['address'] ?? null,
            'place_id' => $data['place_id'],
        ]);

        return $customer->fresh();
    }
}
