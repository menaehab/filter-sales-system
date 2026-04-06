<?php

declare(strict_types=1);

namespace App\Actions\Suppliers;

use App\Models\Supplier;

final class UpdateSupplierAction
{
    public function execute(Supplier $supplier, array $data): Supplier
    {
        $supplier->update([
            'name' => $data['name'],
        ]);

        $supplier->syncPhones($data['phones'] ?? []);

        return $supplier->fresh('phones');
    }
}
