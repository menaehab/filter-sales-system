<?php

namespace App\Livewire\Suppliers;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\HasValidationAttributes;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupplierManagement extends Component
{
    use WithSearchAndPagination;
    use HasForm;
    use HasCrudModals;
    use HasCrudQuery;
    use HasValidationAttributes;

    public function mount()
    {
        $this->resetForm();
    }

    protected function rules()
    {
        return [
            'form.name' => 'required|string|max:255',
            'form.phone' => [
                'nullable',
                'string',
                'max:11',
                'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/',
            ],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.name' => __('keywords.name'),
            'form.phone' => __('keywords.phone'),
        ];
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

    public function create()
    {
        $this->validate();
        Supplier::create($this->form);
        $this->resetForm();
        $this->dispatch('close-modal-create-supplier');
    }

    public function updateSupplier()
    {
        $this->validate();
        Supplier::findOrFail($this->editId)->update($this->form);
        $this->resetForm();
        $this->editId = null;
        $this->dispatch('close-modal-edit-supplier');
    }

    public function setDelete($id)
    {
        $this->openDeleteModal($id, 'open-modal-delete-supplier');
    }

    public function openEdit($id)
    {
        $supplier = Supplier::findOrFail($id);

        $this->openEditModal($supplier->id, 'open-modal-edit-supplier');

        $this->form = [
            'name' => $supplier->name,
            'phone' => $supplier->phone,
        ];
    }

    public function delete()
    {
        Supplier::findOrFail($this->deleteId)->delete();
        $this->deleteId = null;
        $this->dispatch('close-modal-delete-supplier');
        $this->resetPage();
    }

    public function getSuppliersProperty()
    {
        return $this->items;
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-management');
    }
}
