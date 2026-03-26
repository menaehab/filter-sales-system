<?php

namespace App\Livewire\Expenses;

use App\Actions\Expenses\CreateExpenseAction;
use App\Actions\Expenses\DeleteExpenseAction;
use App\Actions\Expenses\UpdateExpenseAction;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Expense;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'expenses_management'])]
class ExpenseManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->resetForm();
    }

    protected function additionalQueryString(): array
    {
        return [
            'dateFrom' => ['as' => 'from', 'except' => ''],
            'dateTo' => ['as' => 'to', 'except' => ''],
        ];
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    protected function applyAdditionalFilters($query): void
    {
        if (filled($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if (filled($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
    }

    #[Computed]
    public function expenses()
    {
        return $this->items;
    }

    #[Computed]
    public function totalExpenses(): float
    {
        $query = Expense::query();

        if (filled($this->search)) {
            $query->where('description', 'like', '%'.$this->search.'%');
        }

        if (filled($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if (filled($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return (float) $query->sum('amount');
    }

    protected function getModelClass(): string
    {
        return Expense::class;
    }

    protected function getDefaultForm(): array
    {
        return [
            'amount' => null,
            'description' => '',
        ];
    }

    public function create(CreateExpenseAction $action): void
    {
        $this->authorizeManageExpenses();

        $request = new \App\Http\Requests\Expenses\CreateExpenseRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $action->execute($validated['form']);

        $this->resetForm();
        $this->dispatch('close-modal-create-expense');
        $this->resetPage();
    }

    public function openEdit($id): void
    {
        $this->authorizeManageExpenses();

        $expense = Expense::findOrFail($id);

        $this->editId = $expense->id;

        $this->form = [
            'amount' => $expense->amount,
            'description' => $expense->description,
        ];

        $this->dispatch('open-modal-edit-expense');
    }

    public function updateExpense(UpdateExpenseAction $action): void
    {
        $this->authorizeManageExpenses();

        $request = new \App\Http\Requests\Expenses\UpdateExpenseRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $expense = Expense::findOrFail($this->editId);
        $action->execute($expense, $validated['form']);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-expense');
        $this->resetPage();
    }

    public function setDelete($id): void
    {
        $this->authorizeManageExpenses();

        $this->openDeleteModal($id, 'open-modal-delete-expense');
    }

    public function delete(DeleteExpenseAction $action): void
    {
        $this->authorizeManageExpenses();

        $expense = Expense::find($this->deleteId);
        if ($expense) {
            $action->execute($expense);
        }

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-expense');
        $this->resetPage();
    }

    protected function getSearchableFields(): array
    {
        return ['description'];
    }

    protected function getWithRelations(): array
    {
        return ['user'];
    }

    public function render()
    {
        return view('livewire.expenses.expense-management');
    }

    public function authorizeManageExpenses(): void
    {
        abort_unless(auth()->user()?->can('manage_expenses'), 403);
    }
}
