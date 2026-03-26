<?php

namespace App\Livewire\Suppliers;

use App\Actions\Suppliers\CreateSupplierAction;
use App\Actions\Suppliers\DeleteSupplierAction;
use App\Actions\Suppliers\UpdateSupplierAction;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Supplier;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupplierManagement extends Component
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
        ];
    }

    protected function getModelClass(): string
    {
        return Supplier::class;
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'phone'];
    }

    public function create(CreateSupplierAction $action): void
    {
        $this->authorizeManageSuppliers();

        $request = new \App\Http\Requests\Suppliers\CreateSupplierRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $action->execute($validated['form']);
        $this->resetForm();
        $this->dispatch('close-modal-create-supplier');
    }

    public function updateSupplier(UpdateSupplierAction $action): void
    {
        $this->authorizeManageSuppliers();

        $request = new \App\Http\Requests\Suppliers\UpdateSupplierRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $supplier = Supplier::findOrFail($this->editId);
        $action->execute($supplier, $validated['form']);
        $this->resetForm();
        $this->editId = null;
        $this->dispatch('close-modal-edit-supplier');
    }

    public function setDelete($id): void
    {
        $this->authorizeManageSuppliers();

        $this->openDeleteModal($id, 'open-modal-delete-supplier');
    }

    public function openEdit($id): void
    {
        $this->authorizeManageSuppliers();

        $supplier = Supplier::findOrFail($id);

        $this->openEditModal($supplier->id, 'open-modal-edit-supplier');

        $this->form = [
            'name' => $supplier->name,
            'phone' => $supplier->phone,
        ];
    }

    public function delete(DeleteSupplierAction $action): void
    {
        $this->authorizeManageSuppliers();

        $supplier = Supplier::find($this->deleteId);
        if ($supplier) {
            $action->execute($supplier);
        }
        $this->deleteId = null;
        $this->dispatch('close-modal-delete-supplier');
        $this->resetPage();
    }

    #[Computed]
    public function suppliers()
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-management');
    }

    protected function authorizeManageSuppliers(): void
    {
        abort_unless(auth()->user()?->can('manage_suppliers'), 403);
    }
}
