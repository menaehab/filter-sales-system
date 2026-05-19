<?php

declare(strict_types=1);

namespace App\Actions\WaterFilters;

use App\Models\WaterFilter;
use App\Models\Technician;
use Illuminate\Validation\ValidationException;

final class CreateWaterFilterAction
{
    public function execute(array $data): WaterFilter
    {
        $isInstalled = (bool) ($data['is_installed'] ?? false);
        $customerId = (int) $data['customer_id'];
        $technician = Technician::find($data['technician_id'] ?? null);

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
            'technician_id' => $isInstalled ? ($data['technician_id'] ?? null) : null,
            'technician_name' => $isInstalled ? $technician?->name : null,
            'customer_id' => $customerId,
            'faucet_type' => blank($data['faucet_type'] ?? null) ? null : $data['faucet_type'],
        ]);
    }
}
