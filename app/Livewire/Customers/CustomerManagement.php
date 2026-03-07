<?php

namespace App\Livewire\Customers;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\HasValidationAttributes;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'customers_management'])]
class CustomerManagement extends Component
{
    use WithSearchAndPagination, HasForm, HasCrudModals, HasCrudQuery, HasValidationAttributes;
    public function mount()
    {
        $this->resetForm();
    }

    protected function rules()
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.phone' => ['nullable', 'string', 'max:11', 'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/'],
            'form.national_number' => ['nullable', 'string', 'max:14', 'min:14'],
            'form.address' => 'nullable|string|max:255',
        ];
    }
    protected function validationAttributes(): array
    {
        return [
            'form.name' => __('keywords.name'),
            'form.phone' => __('keywords.phone'),
            'form.national_number' => __('keywords.national_number'),
            'form.address' => __('keywords.address'),
        ];
    }

    protected function getDefaultForm(): array
    {
        return [
            'name' => '',
            'phone' => '',
            'national_number' => '',
            'address' => '',
        ];
    }

    protected function getModelClass(): string
    {
        return Customer::class;
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'phone', 'national_number', 'address'];
    }

    public function create()
    {
        $this->validate();
        Customer::create($this->form);
        $this->resetForm();
        $this->dispatch('close-modal-create-customer');
    }
    public function updateCustomer()
    {
        $this->validate();
        Customer::findOrFail($this->editId)->update($this->form);
        $this->editId = null;
        $this->dispatch('close-modal-edit-customer');
    }

    public function setDelete($id)
    {
        $this->openDeleteModal($id, 'open-modal-delete-customer');
    }

    public function openEdit($id)
    {
        $customer = Customer::findOrFail($id);

        $this->openEditModal($customer->id, 'open-modal-edit-customer');

        $this->form = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'national_number' => $customer->national_number,
            'address' => $customer->address,
        ];
    }

    public function delete()
    {
        Customer::findOrFail($this->deleteId)->delete();
        $this->deleteId = null;
        $this->dispatch('close-modal-delete-customer');
        $this->resetPage();
    }

    public function getCustomersProperty()
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.customers.customer-management');
    }
}
