<?php

namespace App\Livewire\Expenses;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\HasValidationAttributes;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Expense;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'expenses_management'])]
class ExpenseManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, HasValidationAttributes, WithSearchAndPagination;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount()
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

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
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

    public function getExpensesProperty()
    {
        return $this->items;
    }

    public function getTotalExpensesProperty(): float
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

    protected function rules()
    {
        return [
            'form.amount' => ['required', 'numeric', 'min:0.01'],
            'form.description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.amount' => __('keywords.amount'),
            'form.description' => __('keywords.description'),
        ];
    }

    protected function getDefaultForm(): array
    {
        return [
            'amount' => null,
            'description' => '',
        ];
    }

    public function create()
    {
        $this->authorizeManageExpenses();

        $this->validate();

        Expense::create([
            'amount' => $this->form['amount'],
            'description' => $this->form['description'],
            'user_id' => auth()->id(),
        ]);

        $this->resetForm();
        $this->dispatch('close-modal-create-expense');
        $this->resetPage();
    }

    public function openEdit($id)
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

    public function updateExpense()
    {
        $this->authorizeManageExpenses();

        $this->validate();

        Expense::findOrFail($this->editId)->update($this->form);

        $this->resetForm();
        $this->editId = null;

        $this->dispatch('close-modal-edit-expense');
        $this->resetPage();
    }

    public function setDelete($id)
    {
        $this->authorizeManageExpenses();

        $this->openDeleteModal($id, 'open-modal-delete-expense');
    }

    public function delete()
    {
        $this->authorizeManageExpenses();

        Expense::find($this->deleteId)?->delete();

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

    public function authorizeManageExpenses()
    {
        return auth()->user()->can('manage_expenses');
    }
}
