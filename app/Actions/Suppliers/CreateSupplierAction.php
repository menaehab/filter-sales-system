<?php

declare(strict_types=1);

namespace App\Actions\Suppliers;

use App\Models\Supplier;

final class CreateSupplierAction
{
    public function execute(array $data): Supplier
    {
        $supplier = Supplier::create([
            'name' => $data['name'],
        ]);

        $supplier->syncPhones($data['phones'] ?? []);

        return $supplier->fresh('phones');
    }
}
