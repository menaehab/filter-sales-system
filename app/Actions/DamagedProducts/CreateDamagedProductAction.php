<?php

declare(strict_types=1);

namespace App\Actions\DamagedProducts;

use App\Models\DamagedProduct;
use App\Models\Product;
use App\Models\ProductMovement;
use Illuminate\Support\Facades\DB;

final class CreateDamagedProductAction
{
    public function execute(array $data): DamagedProduct
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);

            $damage = DamagedProduct::create([
                'product_id' => $data['product_id'],
                'cost_price' => $product->cost_price,
                'quantity' => $data['quantity'],
                'reason' => $data['reason'] ?? null,
                'user_id' => auth()->id(),
            ]);

            $product->decrement('quantity', $data['quantity']);

            ProductMovement::create([
                'quantity' => -$data['quantity'],
                'movable_type' => DamagedProduct::class,
                'movable_id' => $damage->id,
                'product_id' => $data['product_id'],
            ]);

            return $damage;
        });
    }
}
