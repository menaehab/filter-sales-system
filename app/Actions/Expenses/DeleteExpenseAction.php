<?php

declare(strict_types=1);

namespace App\Actions\Expenses;

use App\Models\Expense;

final class DeleteExpenseAction
{
    public function execute(Expense $expense): void
    {
        $expense->delete();
    }
}
