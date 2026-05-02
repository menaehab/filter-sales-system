<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\WaterFilter;

class WaterFilterObserver
{
    /**
     * Handle the WaterFilter "saved" event.
     */
    public function saved(WaterFilter $waterFilter): void
    {
        if (! $waterFilter->wasChanged('installed_at')) {
            return;
        }

        if (! $waterFilter->installed_at || ! $waterFilter->customer_id) {
            return;
        }

        $installmentStartDate = $waterFilter->installed_at->format('Y-m-d');

        Sale::query()
            ->where('customer_id', $waterFilter->customer_id)
            ->where('payment_type', 'installment')
            ->where(function ($query) {
                $query->whereNull('installment_start_date')
                    ->orWhereRaw('DATE(installment_start_date) = DATE(created_at)');
            })
            ->update(['installment_start_date' => $installmentStartDate]);
    }
}
