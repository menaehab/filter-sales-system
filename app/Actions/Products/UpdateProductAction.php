<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;

final class UpdateProductAction
{
    public function execute(Product $product, array $data): Product
    {
        $product->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'cost_price' => (float) ($data['cost_price'] ?? $product->cost_price),
            'quantity' => (int) ($data['quantity'] ?? $product->quantity),
            'min_quantity' => (int) ($data['min_quantity'] ?? $product->min_quantity),
            'category_id' => (int) $data['category_id'],
        ]);

        return $product->fresh();
    }
}
