<?php

declare(strict_types=1);

namespace App\Actions\Purchases;

use App\Models\Purchase;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;

final class DeletePurchaseAction
{
    public function execute(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $relatedPaymentIds = $purchase->paymentAllocations
                ->pluck('supplier_payment_id')
                ->unique()
                ->all();

            $purchase->delete();

            if (!empty($relatedPaymentIds)) {
                SupplierPayment::whereIn('id', $relatedPaymentIds)
                    ->doesntHave('allocations')
                    ->delete();
            }
        });
    }
}
