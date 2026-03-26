<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;

final class CreateCategoryAction
{
    public function execute(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
        ]);
    }
}
