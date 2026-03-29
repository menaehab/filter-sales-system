<?php

declare(strict_types=1);

namespace App\Actions\Places;

use App\Models\Place;

final class DeletePlaceAction
{
    public function execute(Place $place): void
    {
        $place->delete();
    }
}
