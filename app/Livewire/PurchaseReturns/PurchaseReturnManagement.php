<?php

namespace App\Livewire\PurchaseReturns;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\PurchaseReturn;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseReturnManagement extends Component
{
    use WithSearchAndPagination, HasForm, HasCrudModals, HasCrudQuery;

    public function mount()
    {
        $this->resetForm();
    }

    protected function rules()
    {
        return [];
    }

    protected function getDefaultForm(): array
    {
        return [];
    }

    protected function getModelClass(): string
    {
        return PurchaseReturn::class;
    }

    protected function getSearchableFields(): array
    {
        return ['number'];
    }

    protected function getWithRelations(): array
    {
        return ['purchase', 'items.product', 'user'];
    }

    public function getPurchaseReturnsProperty()
    {
        return $this->items;
    }

    public function setDelete($id)
    {
        $this->authorizeManagePurchaseReturns();
        $this->openDeleteModal($id, 'open-modal-delete-purchase-return');
    }

    public function delete(): void
    {
        $this->authorizeManagePurchaseReturns();

        $purchaseReturn = PurchaseReturn::with('items')->findOrFail($this->deleteId);

        foreach ($purchaseReturn->items as $item) {
            $item->product?->increment('quantity', $item->quantity);
        }

        $purchaseReturn->delete();
        $this->deleteId = null;
        $this->dispatch('close-modal-delete-purchase-return');
        $this->resetPage();
    }

    protected function authorizeManagePurchaseReturns(): void
    {
        abort_unless(auth()->user()?->can('manage_purchase_returns'), 403);
    }

    public function render()
    {
        return view('livewire.purchase-returns.purchase-return-management');
    }
}
