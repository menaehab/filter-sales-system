<?php

namespace App\Observers;

use App\Models\SupplierPayment;

class SupplierPaymentObserver
{
    /**
     * Handle the SupplierPayment "created" event.
     */
    public function creating(SupplierPayment $supplierPayment): void
    {
        // only set user_id from auth if it's not already provided (e.g. by a factory)
        if (is_null($supplierPayment->user_id)) {
            $supplierPayment->user_id = auth()->id();
        }
    }

    /**
     * Handle the SupplierPayment "updated" event.
     */
    public function updated(SupplierPayment $supplierPayment): void
    {
        //
    }

    /**
     * Handle the SupplierPayment "deleted" event.
     */
    public function deleted(SupplierPayment $supplierPayment): void
    {
        //
    }

    /**
     * Handle the SupplierPayment "restored" event.
     */
    public function restored(SupplierPayment $supplierPayment): void
    {
        //
    }

    /**
     * Handle the SupplierPayment "force deleted" event.
     */
    public function forceDeleted(SupplierPayment $supplierPayment): void
    {
        //
    }
}
