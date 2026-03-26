<?php

declare(strict_types=1);

namespace App\Actions\SaleReturns;

use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;

final class DeleteSaleReturnAction
{
    public function execute(SaleReturn $saleReturn): void
    {
        DB::transaction(function () use ($saleReturn) {
            $saleReturn->delete();
        });
    }
}
