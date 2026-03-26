<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;

final class DeleteProductAction
{
    public function execute(Product $product): void
    {
        $product->delete();
    }
}
