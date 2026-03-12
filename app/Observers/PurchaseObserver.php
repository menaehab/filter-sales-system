<?php

namespace App\Observers;

use App\Models\Purchase;

class PurchaseObserver
{
    /**
     * Handle the Purchase "created" event.
     */
    public function creating(Purchase $purchase): void
    {
        $today = now()->format('Ymd');

        $lastPurchase = Purchase::where('number', 'like', $today . '-%')
            ->orderByDesc('number')
            ->first();

        $nextNumber = 1;

        if ($lastPurchase) {
            $lastSequence = (int) explode('-', $lastPurchase->number)[1];
            $nextNumber = $lastSequence + 1;
        }

        $purchase->number = $today . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Handle the Purchase "updated" event.
     */
    public function updated(Purchase $purchase): void
    {
        //
    }

    /**
     * Handle the Purchase "deleted" event.
     */
    public function deleted(Purchase $purchase): void
    {
        //
    }

    /**
     * Handle the Purchase "restored" event.
     */
    public function restored(Purchase $purchase): void
    {
        //
    }

    /**
     * Handle the Purchase "force deleted" event.
     */
    public function forceDeleted(Purchase $purchase): void
    {
        //
    }
}
