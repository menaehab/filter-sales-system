<?php

declare(strict_types=1);

namespace App\Actions\WaterFilters;

use App\Models\WaterFilter;

final class DeleteWaterFilterAction
{
    public function execute(WaterFilter $filter): void
    {
        $filter->delete();
    }
}
