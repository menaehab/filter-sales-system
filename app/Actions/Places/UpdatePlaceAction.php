<?php

declare(strict_types=1);

namespace App\Actions\Places;

use App\Models\Place;

final class UpdatePlaceAction
{
    public function execute(Place $place, array $data): Place
    {
        $place->update([
            'name' => $data['name'],
        ]);

        $place->users()->sync($data['user_ids'] ?? []);

        return $place;
    }
}
