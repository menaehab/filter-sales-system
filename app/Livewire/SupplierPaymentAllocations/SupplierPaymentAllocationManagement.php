<?php

namespace App\Livewire\SupplierPaymentAllocations;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Livewire\Traits\HasCrudQuery;
use App\Models\SupplierPaymentAllocation;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupplierPaymentAllocationManagement extends Component
{
    use HasCrudModals, WithSearchAndPagination, HasCrudQuery;

    // model/query configuration
    protected function getModelClass(): string
    {
        return SupplierPaymentAllocation::class;
    }

    protected function getSearchableFields(): array
    {
        return ['supplierPayment.user.name', 'supplierPayment.supplier.name', 'purchase.number', 'purchase.supplier_name'];
    }

    protected function getWithRelations(): array
    {
        return ['supplierPayment.user', 'supplierPayment.supplier', 'purchase'];
    }

    // used for editing by id
    public function getSupplierPaymentAllocationProperty()
    {
        return SupplierPaymentAllocation::with($this->getWithRelations())
            ->where('id', $this->editId)
            ->first();
    }

    // expose paginated result to the view
    public function getSupplierPaymentAllocationsProperty()
    {
        return $this->items;
    }
    public function setEdit($id)
    {
        $this->authorizeManageSupplierPaymentAllocations();
        $this->openEditModal($id, 'open-modal-edit-supplier-payment-allocation');
    }
    public function setDelete($id)
    {
        $this->authorizeManageSupplierPaymentAllocations();
        $this->openDeleteModal($id, 'open-modal-delete-supplier-payment-allocation');
    }
    public function delete()
    {
        $this->authorizeManageSupplierPaymentAllocations();

        SupplierPaymentAllocation::find($this->deleteId)?->delete();

        $this->deleteId = null;

        $this->dispatch('close-modal-delete-supplier-payment-allocation');
        $this->resetPage();
    }
    public function render()
    {
        return view('livewire.supplier-payment-allocations.supplier-payment-allocation-management');
    }
    private function authorizeManageSupplierPaymentAllocations()
    {
        abort_unless(auth()->user()->can('manage_supplier_payment_allocations'), 403);
    }
}
