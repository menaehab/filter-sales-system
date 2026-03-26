<?php

namespace App\Livewire\Filters;

use App\Actions\WaterFilters\CreateWaterFilterAction;
use App\Actions\WaterFilters\DeleteWaterFilterAction;
use App\Actions\WaterFilters\UpdateWaterFilterAction;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Customer;
use App\Models\WaterFilter;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class FilterManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public $customerSlug = '';

    public $customerSearch = '';

    public $customerModalSearch = '';

    public function mount(): void
    {
        $this->resetForm();

        if ($this->customerSlug) {
            $this->customerSearch = $this->customers->firstWhere('slug', $this->customerSlug)?->name ?? '';
        }
    }

    protected function getDefaultForm(): array
    {
        return [
            'filter_model' => '',
            'address' => '',
            'customer_id' => null,
        ];
    }

    protected function getModelClass(): string
    {
        return WaterFilter::class;
    }

    protected function getSearchableFields(): array
    {
        return ['filter_model', 'address', 'customer.name', 'customer.phone'];
    }

    protected function getWithRelations(): array
    {
        return ['customer'];
    }

    protected function additionalQueryString(): array
    {
        return [
            'customerSlug' => ['as' => 'customer', 'except' => ''],
        ];
    }

    public function updatingCustomerSlug(): void
    {
        $this->resetPage();
        $this->customerSearch = $this->customers->firstWhere('slug', $this->customerSlug)?->name ?? '';
    }

    protected function applyAdditionalFilters($query): void
    {
        if ($this->customerSlug) {
            $query->whereHas('customer', fn ($q) => $q->where('slug', $this->customerSlug));
        }
    }

    public function create(CreateWaterFilterAction $action): void
    {
        $this->authorizeManageFilters();

        $request = new \App\Http\Requests\WaterFilters\CreateWaterFilterRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $action->execute($validated['form']);
        $this->resetForm();
        $this->customerModalSearch = '';
        $this->dispatch('close-modal-create-filter');
        $this->resetPage();
    }

    public function updateFilter(UpdateWaterFilterAction $action): void
    {
        $this->authorizeManageFilters();

        $request = new \App\Http\Requests\WaterFilters\UpdateWaterFilterRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $filter = WaterFilter::findOrFail($this->editId);
        $action->execute($filter, $validated['form']);
        $this->resetForm();
        $this->editId = null;
        $this->customerModalSearch = '';
        $this->dispatch('close-modal-edit-filter');
        $this->resetPage();
    }

    public function setDelete($id): void
    {
        $this->authorizeManageFilters();

        $this->openDeleteModal($id, 'open-modal-delete-filter');
    }

    public function openEdit($id): void
    {
        $this->authorizeManageFilters();

        $filter = WaterFilter::with('customer')->findOrFail($id);

        $this->openEditModal($filter->id, 'open-modal-edit-filter');

        $this->form = [
            'filter_model' => $filter->filter_model,
            'address' => $filter->address,
            'customer_id' => $filter->customer_id,
        ];

        $this->customerModalSearch = $filter->customer?->name ?? '';
    }

    public function delete(DeleteWaterFilterAction $action): void
    {
        $this->authorizeManageFilters();

        $filter = WaterFilter::find($this->deleteId);
        if ($filter) {
            $action->execute($filter);
        }
        $this->deleteId = null;
        $this->dispatch('close-modal-delete-filter');
        $this->resetPage();
    }

    #[Computed]
    public function filters()
    {
        return $this->items;
    }

    #[Computed]
    public function customers(): Collection
    {
        return Customer::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.filters.filter-management');
    }

    protected function authorizeManageFilters(): void
    {
        abort_unless(auth()->user()?->can('manage_water_filters'), 403);
    }
}
