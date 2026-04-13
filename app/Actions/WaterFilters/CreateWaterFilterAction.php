<?php

declare(strict_types=1);

namespace App\Actions\WaterFilters;

use App\Models\WaterFilter;

final class CreateWaterFilterAction
{
    public function execute(array $data): WaterFilter
    {
        $isInstalled = (bool) ($data['is_installed'] ?? false);

        return WaterFilter::create([
            'filter_model' => $data['filter_model'],
            'address' => $data['address'] ?? null,
            'is_installed' => $isInstalled,
            'installed_at' => $isInstalled ? ($data['installed_at'] ?? null) : null,
            'customer_id' => (int) $data['customer_id'],
        ]);
    }
}
