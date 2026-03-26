<?php

declare(strict_types=1);

namespace App\Actions\DamagedProducts;

use App\Models\DamagedProduct;
use App\Models\ProductMovement;
use Illuminate\Support\Facades\DB;

final class DeleteDamagedProductAction
{
    public function execute(DamagedProduct $damagedProduct): void
    {
        DB::transaction(function () use ($damagedProduct) {
            // Restore product stock
            $damagedProduct->product?->increment('quantity', $damagedProduct->quantity);

            // Delete movement records
            ProductMovement::where('movable_type', DamagedProduct::class)
                ->where('movable_id', $damagedProduct->id)
                ->delete();

            $damagedProduct->delete();
        });
    }
}
