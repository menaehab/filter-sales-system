<?php

declare(strict_types=1);

namespace App\Actions\Suppliers;

use App\Models\Supplier;

final class DeleteSupplierAction
{
    public function execute(Supplier $supplier): void
    {
        $supplier->delete();
    }
}
