<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SupplierManagement extends Component
{
    use WithPagination;

    public $form = [
        'name' => '',
        'phone' => '',
    ];

    public $search = '';
    public $perPage = 10;
    public $editId = null;
    public $deleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];

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

    protected function getValidationAttributes()
    {
        return [
            'form.name' => __('keywords.name'),
            'form.phone' => __('keywords.phone'),
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'phone' => '',
        ];
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
        // do not reset editId until after update, otherwise we have no id to look up
        $this->validate();
        Supplier::findOrFail($this->editId)->update($this->form);
        $this->resetForm();
        $this->editId = null;
        $this->dispatch('close-modal-edit-supplier');
    }

    public function setDelete($id)
    {
        $this->deleteId = $id;
        $this->dispatch('open-modal-delete-supplier');
    }
    public function openEdit($id)
    {
        $supplier = Supplier::findOrFail($id);

        $this->editId = $supplier->id;

        $this->form = [
            'name' => $supplier->name,
            'phone' => $supplier->phone,
        ];
        $this->dispatch('open-modal-edit-supplier');
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
        return Supplier::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('phone', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-management');
    }
}
