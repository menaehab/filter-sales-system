<?php

declare(strict_types=1);

namespace App\Actions\PurchaseReturns;

use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\DB;

final class DeletePurchaseReturnAction
{
    public function execute(PurchaseReturn $purchaseReturn): void
    {
        DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn->delete();
        });
    }
}
