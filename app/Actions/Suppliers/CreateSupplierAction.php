<?php

declare(strict_types=1);

namespace App\Actions\Suppliers;

use App\Models\Supplier;

final class CreateSupplierAction
{
    public function execute(array $data): Supplier
    {
        return Supplier::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ]);
    }
}
