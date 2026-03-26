<?php

declare(strict_types=1);

namespace App\Actions\DamagedProducts;

use App\Models\DamagedProduct;
use App\Models\Product;
use App\Models\ProductMovement;
use Illuminate\Support\Facades\DB;

final class UpdateDamagedProductAction
{
    public function execute(DamagedProduct $damagedProduct, array $data): DamagedProduct
    {
        return DB::transaction(function () use ($damagedProduct, $data) {
            $oldQuantity = $damagedProduct->quantity;
            $oldProductId = $damagedProduct->product_id;
            $newProductId = $data['product_id'];
            $newQuantity = $data['quantity'];

            $newProduct = Product::findOrFail($newProductId);

            // Restore old product stock if product changed, or adjust difference
            if ($oldProductId == $newProductId) {
                $quantityDiff = $newQuantity - $oldQuantity;
                $newProduct->decrement('quantity', $quantityDiff);
            } else {
                // Restore old product
                Product::find($oldProductId)?->increment('quantity', $oldQuantity);
                // Decrement new product
                $newProduct->decrement('quantity', $newQuantity);
            }

            $damagedProduct->update([
                'product_id' => $newProductId,
                'cost_price' => $newProduct->cost_price,
                'quantity' => $newQuantity,
                'reason' => $data['reason'] ?? null,
            ]);

            // Recreate movement
            ProductMovement::where('movable_type', DamagedProduct::class)
                ->where('movable_id', $damagedProduct->id)
                ->delete();

            ProductMovement::create([
                'quantity' => -$newQuantity,
                'movable_type' => DamagedProduct::class,
                'movable_id' => $damagedProduct->id,
                'product_id' => $newProductId,
            ]);

            return $damagedProduct->fresh();
        });
    }
}
