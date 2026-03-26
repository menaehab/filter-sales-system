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
            'install_date' => $data['install_date'] ?? null,
            'next_maintenance_date' => $data['next_maintenance_date'] ?? null,
            'customer_id' => (int) $data['customer_id'],
        ]);
    }
}
