<?php

declare(strict_types=1);

namespace App\Actions\SupplierPayments;

use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;

final class DeleteSupplierPaymentAction
{
    public function execute(SupplierPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            // Delete allocations first (cascades via relationship)
            $payment->allocations()->delete();

            // Delete the payment
            $payment->delete();
        });
    }
}
