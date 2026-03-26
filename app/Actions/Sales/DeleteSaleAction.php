<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Models\CustomerPayment;
use App\Models\ProductMovement;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

final class DeleteSaleAction
{
    public function execute(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale->loadMissing(['items', 'paymentAllocations']);

            $relatedPaymentIds = $sale->paymentAllocations
                ->pluck('customer_payment_id')
                ->unique()
                ->all();

            // Restore product quantities
            foreach ($sale->items as $item) {
                $item->product?->increment('quantity', $item->quantity);
            }

            // Delete product movements
            ProductMovement::where('movable_type', Sale::class)
                ->where('movable_id', $sale->id)
                ->delete();

            // Delete sale (cascades to items and allocations)
            $sale->delete();

            // Delete orphaned payments
            if (!empty($relatedPaymentIds)) {
                CustomerPayment::whereIn('id', $relatedPaymentIds)
                    ->doesntHave('allocations')
                    ->delete();
            }
        });
    }
}
