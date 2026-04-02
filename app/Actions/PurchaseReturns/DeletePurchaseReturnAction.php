<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturns;

use App\Models\ProductMovement;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;

final class DeletePurchaseReturnAction
{
    public function execute(PurchaseReturn $purchaseReturn): void
    {
        DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn->loadMissing('items.product');

            foreach ($purchaseReturn->items as $item) {
                $item->product?->increment('quantity', $item->quantity);
            }

            ProductMovement::where('movable_type', PurchaseReturn::class)
                ->where('movable_id', $purchaseReturn->id)
                ->delete();

            $purchaseReturn->delete();
        });
    }
}
