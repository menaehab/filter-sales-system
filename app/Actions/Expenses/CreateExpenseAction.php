<?php

declare(strict_types=1);

namespace App\Actions\Expenses;

use App\Models\Expense;

final class CreateExpenseAction
{
    public function execute(array $data): Expense
    {
        return Expense::create([
            'amount' => (float) $data['amount'],
            'description' => $data['description'] ?? null,
            'user_id' => auth()->id(),
        ]);
    }
}
