<?php

declare(strict_types=1);

namespace App\Actions\Places;

use App\Models\Place;

final class CreatePlaceAction
{
    public function execute(array $data): Place
    {
        $place = Place::create([
            'name' => $data['name'],
        ]);

        $place->users()->sync($data['user_ids'] ?? []);

        return $place;
    }
}
