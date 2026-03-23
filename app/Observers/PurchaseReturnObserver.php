<?php

namespace App\Observers;

use App\Models\PurchaseReturn;

class PurchaseReturnObserver
{
    /**
     * Handle the PurchaseReturn "created" event.
     */
    public function creating(PurchaseReturn $purchaseReturn): void
    {
        $today = now()->format('Ymd');

        $lastPurchase = PurchaseReturn::where('number', 'like', $today.'-%')
            ->orderByDesc('number')
            ->first();

        $nextNumber = 1;

        if ($lastPurchase) {
            $lastSequence = (int) explode('-', $lastPurchase->number)[1];
            $nextNumber = $lastSequence + 1;
        }

        $purchaseReturn->number = $today.'-'.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Handle the PurchaseReturn "updated" event.
     */
    public function updated(PurchaseReturn $purchaseReturn): void
    {
        //
    }

    /**
     * Handle the PurchaseReturn "deleted" event.
     */
    public function deleted(PurchaseReturn $purchaseReturn): void
    {
        //
    }

    /**
     * Handle the PurchaseReturn "restored" event.
     */
    public function restored(PurchaseReturn $purchaseReturn): void
    {
        //
    }

    /**
     * Handle the PurchaseReturn "force deleted" event.
     */
    public function forceDeleted(PurchaseReturn $purchaseReturn): void
    {
        //
    }
}
