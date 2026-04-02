<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Models\Product;
use App\Models\ProductMovement;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class UpdatePurchaseAction
{
    public function execute(Purchase $purchase, array $data): Purchase
    {
        $supplier = Supplier::findOrFail($data['supplier_id']);
        $totalPrice = $this->calculateTotalPrice($data['items']);
        $isInstallment = ($data['payment_type'] ?? 'cash') === 'installment';
        $months = $isInstallment ? (int) ($data['installment_months'] ?? 0) : null;
        $remainingAfterDown = max(0, $totalPrice - (float) ($data['down_payment'] ?? 0));
        $installmentAmount = ($months && $months > 0) ? round($remainingAfterDown / $months, 2) : null;

        return DB::transaction(function () use (
            $purchase,
            $supplier,
            $totalPrice,
            $isInstallment,
            $months,
            $installmentAmount,
            $data
        ) {
            $this->restoreOldStock($purchase);
            $this->deleteOldItemsAndMovements($purchase);

            $purchase->update([
                'supplier_name' => $supplier->name,
                'total_price' => $totalPrice,
                'payment_type' => $isInstallment ? 'installment' : 'cash',
                'installment_amount' => $installmentAmount,
                'installment_months' => $months,
                'supplier_id' => $supplier->id,
                'created_at' => $this->resolveCreatedAt($purchase, data_get($data, 'created_at')),
            ]);

            $this->createNewItems($purchase, $data['items']);

            return $purchase->fresh();
        });
    }

    private function resolveCreatedAt(Purchase $purchase, mixed $createdAt): CarbonInterface
    {
        if (auth()->user()?->can('manage_created_at') && filled($createdAt)) {
            return Carbon::parse((string) $createdAt);
        }

        return $purchase->created_at;
    }

    private function calculateTotalPrice(array $items): float
    {
        return collect($items)->sum(function ($item) {
            return ((float) ($item['cost_price'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
        });
    }

    private function restoreOldStock(Purchase $purchase): void
    {
        foreach ($purchase->items as $oldItem) {
            $product = Product::find($oldItem->product_id);
            if ($product) {
                $product->decrement('quantity', $oldItem->quantity);
            }
        }
    }

    private function deleteOldItemsAndMovements(Purchase $purchase): void
    {
        ProductMovement::where('movable_type', Purchase::class)
            ->where('movable_id', $purchase->id)
            ->delete();

        $purchase->items()->delete();
    }

    private function createNewItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $quantity = (int) $item['quantity'];

            PurchaseItem::create([
                'product_name' => $product->name,
                'cost_price' => (float) $item['cost_price'],
                'quantity' => $quantity,
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
            ]);

            $product->update(['cost_price' => (float) $item['cost_price']]);
            $product->increment('quantity', $quantity);

            ProductMovement::create([
                'quantity' => $quantity,
                'movable_type' => Purchase::class,
                'movable_id' => $purchase->id,
                'product_id' => $product->id,
            ]);
        }
    }
}
