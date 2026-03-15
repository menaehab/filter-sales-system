<?php

namespace App\Observers;

use App\Models\Sale;

class SaleObserver
{
    /**
     * Handle the Sale "creating" event.
     */
    public function creating(Sale $sale): void
    {
        $today = now()->format('Ymd');

        $lastSale = Sale::where('number', 'like', $today . '-%')
            ->orderByDesc('number')
            ->first();

        $nextNumber = 1;

        if ($lastSale) {
            $lastSequence = (int) explode('-', $lastSale->number)[1];
            $nextNumber = $lastSequence + 1;
        }

        $sale->number = $today . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $user = auth()->user();
        $sale->user_id = $user?->id;
        $sale->user_name = $user?->name;
    }
}
