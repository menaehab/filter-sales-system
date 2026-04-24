<?php

declare(strict_types=1);

namespace App\Actions\WaterFilters;

use App\Models\WaterFilter;
use Illuminate\Validation\ValidationException;

final class UpdateWaterFilterAction
{
    public function execute(WaterFilter $filter, array $data): WaterFilter
    {
        $isInstalled = (bool) ($data['is_installed'] ?? false);
        $customerId = (int) $data['customer_id'];

        if (WaterFilter::where('customer_id', $customerId)->whereKeyNot($filter->id)->exists()) {
            throw ValidationException::withMessages([
                'customer_id' => __('validation.unique', ['attribute' => __('keywords.customer')]),
            ]);
        }

        $filter->update([
            'filter_model' => $data['filter_model'],
            'address' => $data['address'] ?? null,
            'is_installed' => $isInstalled,
            'installed_at' => $isInstalled ? ($data['installed_at'] ?? null) : null,
            'customer_id' => $customerId,
        ]);

        return $filter->fresh();
    }
}
