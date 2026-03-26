<?php

declare(strict_types=1);

namespace App\Actions\Expenses;

use App\Models\Expense;

final class UpdateExpenseAction
{
    public function execute(Expense $expense, array $data): Expense
    {
        $expense->update([
            'amount' => (float) $data['amount'],
            'description' => $data['description'] ?? null,
        ]);

        return $expense->fresh();
    }
}
