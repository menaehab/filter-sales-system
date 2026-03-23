<?php

namespace App\Livewire\Filters;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\HasValidationAttributes;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Customer;
use App\Models\WaterFilter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class FilterManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, HasValidationAttributes, WithSearchAndPagination;

    public $customers;

    public $customerSlug = '';

    public $customerSearch = '';

    public $customerModalSearch = '';

    public function mount()
    {
        $this->resetForm();
        $this->customers = Customer::orderBy('name')->get();

        if ($this->customerSlug) {
            $this->customerSearch = $this->customers->firstWhere('slug', $this->customerSlug)?->name ?? '';
        }
    }

    protected function rules()
    {
        return [
            'form.filter_model' => 'required|string|max:255',
            'form.address' => 'required|string|max:255',
            'form.customer_id' => 'required|exists:customers,id',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.filter_model' => __('keywords.filter_model'),
            'form.address' => __('keywords.address'),
            'form.customer_id' => __('keywords.customer'),
        ];
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

    public function updatingCustomerSlug()
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

    public function create()
    {
        $this->authorizeManageFilters();

        $this->validate();
        WaterFilter::create($this->form);
        $this->resetForm();
        $this->customerModalSearch = '';
        $this->dispatch('close-modal-create-filter');
        $this->resetPage();
    }

    public function updateFilter()
    {
        $this->authorizeManageFilters();

        $this->validate();
        WaterFilter::findOrFail($this->editId)->update($this->form);
        $this->resetForm();
        $this->editId = null;
        $this->customerModalSearch = '';
        $this->dispatch('close-modal-edit-filter');
        $this->resetPage();
    }

    public function setDelete($id)
    {
        $this->authorizeManageFilters();

        $this->openDeleteModal($id, 'open-modal-delete-filter');
    }

    public function openEdit($id)
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

    public function delete()
    {
        $this->authorizeManageFilters();

        WaterFilter::findOrFail($this->deleteId)->delete();
        $this->deleteId = null;
        $this->dispatch('close-modal-delete-filter');
        $this->resetPage();
    }

    public function getFiltersProperty()
    {
        return $this->items;
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
