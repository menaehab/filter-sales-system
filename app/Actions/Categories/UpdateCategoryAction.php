<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;

final class UpdateCategoryAction
{
    public function execute(Category $category, array $data): Category
    {
        $category->update([
            'name' => $data['name'],
        ]);

        return $category->fresh();
    }
}
