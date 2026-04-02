<?php

declare(strict_types=1);

namespace App\Actions\WaterFilters;

use App\Models\WaterFilter;

final class CreateWaterFilterAction
{
    public function execute(array $data): WaterFilter
    {
        return WaterFilter::create([
            'filter_model' => $data['filter_model'],
            'address' => $data['address'] ?? null,
            'installed_at' => $data['installed_at'] ?? null,
            'customer_id' => (int) $data['customer_id'],
        ]);
    }
}
