<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturns;

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;

final class CreatePurchaseReturnAction
{
    public function execute(int $purchaseId, array $data): PurchaseReturn
    {
        $purchase = Purchase::findOrFail($purchaseId);

        return DB::transaction(function () use ($purchase, $data) {
            $totalPrice = $this->calculateTotalPrice($data['items']);

            $purchaseReturn = PurchaseReturn::create([
                'total_price' => $totalPrice,
                'reason' => $data['reason'] ?? null,
                'cash_refund' => (bool) ($data['cash_refund'] ?? false),
                'purchase_id' => $purchase->id,
                'user_id' => auth()->id(),
            ]);

            $this->createReturnItems($purchaseReturn, $data['items']);

            return $purchaseReturn;
        });
    }

    private function calculateTotalPrice(array $items): float
    {
        return collect($items)
            ->filter(fn ($item) => $item['selected'] ?? false)
            ->sum(fn ($item) => ((float) ($item['cost_price'] ?? 0)) * ((int) ($item['return_quantity'] ?? 0)));
    }

    private function createReturnItems(PurchaseReturn $purchaseReturn, array $items): void
    {
        foreach ($items as $item) {
            if (!($item['selected'] ?? false) || ((int) ($item['return_quantity'] ?? 0)) <= 0) {
                continue;
            }

            $quantity = (int) $item['return_quantity'];

            PurchaseReturnItem::create([
                'cost_price' => (float) $item['cost_price'],
                'quantity' => $quantity,
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $item['product_id'],
            ]);

            $product = Product::findOrFail($item['product_id']);
            $product->decrement('quantity', $quantity);

            ProductMovement::create([
                'quantity' => -$quantity,
                'movable_type' => PurchaseReturn::class,
                'movable_id' => $purchaseReturn->id,
                'product_id' => $item['product_id'],
            ]);
        }
    }
}
