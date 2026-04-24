<?php

declare(strict_types=1);

namespace App\Actions\WaterFilters;

use App\Models\WaterFilter;
use Illuminate\Validation\ValidationException;

final class CreateWaterFilterAction
{
    public function execute(array $data): WaterFilter
    {
        $isInstalled = (bool) ($data['is_installed'] ?? false);
        $customerId = (int) $data['customer_id'];

        if (WaterFilter::where('customer_id', $customerId)->exists()) {
            throw ValidationException::withMessages([
                'customer_id' => __('validation.unique', ['attribute' => __('keywords.customer')]),
            ]);
        }

        return WaterFilter::create([
            'filter_model' => $data['filter_model'],
            'address' => $data['address'] ?? null,
            'is_installed' => $isInstalled,
            'installed_at' => $isInstalled ? ($data['installed_at'] ?? null) : null,
            'customer_id' => $customerId,
        ]);
    }
}
