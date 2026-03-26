<?php

namespace App\Livewire\Customers;

use App\Actions\Customers\CreateCustomerAction;
use App\Actions\Customers\DeleteCustomerAction;
use App\Actions\Customers\UpdateCustomerAction;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Customer;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'customers_management'])]
class CustomerManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public function mount(): void
    {
        $this->resetForm();
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

    public function create(CreateCustomerAction $action): void
    {
        $this->authorizeManageCustomers();

        $request = new \App\Http\Requests\Customers\CreateCustomerRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $action->execute($validated['form']);
        $this->resetForm();
        $this->dispatch('close-modal-create-customer');
    }

    public function updateCustomer(UpdateCustomerAction $action): void
    {
        $this->authorizeManageCustomers();

        $request = new \App\Http\Requests\Customers\UpdateCustomerRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $customer = Customer::findOrFail($this->editId);
        $action->execute($customer, $validated['form']);
        $this->editId = null;
        $this->dispatch('close-modal-edit-customer');
    }

    public function setDelete($id): void
    {
        $this->authorizeManageCustomers();

        $this->openDeleteModal($id, 'open-modal-delete-customer');
    }

    public function openEdit($id): void
    {
        $this->authorizeManageCustomers();

        $customer = Customer::findOrFail($id);

        $this->openEditModal($customer->id, 'open-modal-edit-customer');

        $this->form = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'national_number' => $customer->national_number,
            'address' => $customer->address,
        ];
    }

    public function delete(DeleteCustomerAction $action): void
    {
        $this->authorizeManageCustomers();

        $customer = Customer::find($this->deleteId);
        if ($customer) {
            $action->execute($customer);
        }
        $this->deleteId = null;
        $this->dispatch('close-modal-delete-customer');
        $this->resetPage();
    }

    #[Computed]
    public function customers()
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.customers.customer-management');
    }

    protected function authorizeManageCustomers(): void
    {
        abort_unless(auth()->user()?->can('manage_customers'), 403);
    }
}
