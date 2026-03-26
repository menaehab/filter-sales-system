<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Models\Customer;

final class DeleteCustomerAction
{
    public function execute(Customer $customer): void
    {
        $customer->delete();
    }
}
