<?php

declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;

final class CreateProductAction
{
    public function execute(array $data): Product
    {
        return Product::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'cost_price' => (float) ($data['cost_price'] ?? 0),
            'quantity' => (int) ($data['quantity'] ?? 0),
            'min_quantity' => (int) ($data['min_quantity'] ?? 0),
            'category_id' => (int) $data['category_id'],
            'for_maintenance' => (bool) ($data['for_maintenance'] ?? false),
        ]);
    }
}
