<?php

namespace App\Livewire\SaleReturns;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\ProductMovement;
use App\Models\SaleReturn;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleReturnManagement extends Component
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
        return SaleReturn::class;
    }

    protected function getSearchableFields(): array
    {
        return ['number'];
    }

    protected function getWithRelations(): array
    {
        return ['sale', 'items.product', 'user'];
    }

    public function getSaleReturnsProperty()
    {
        return $this->items;
    }

    public function setDelete($id)
    {
        $this->authorizeManageSaleReturns();
        $this->openDeleteModal($id, 'open-modal-delete-sale-return');
    }

    public function delete(): void
    {
        $this->authorizeManageSaleReturns();

        $saleReturn = SaleReturn::with('items')->findOrFail($this->deleteId);

        foreach ($saleReturn->items as $item) {
            $item->product?->decrement('quantity', $item->quantity);
        }

        ProductMovement::where('movable_type', SaleReturn::class)
            ->where('movable_id', $saleReturn->id)
            ->delete();

        $saleReturn->delete();
        $this->deleteId = null;
        $this->dispatch('close-modal-delete-sale-return');
        $this->resetPage();
    }

    protected function authorizeManageSaleReturns(): void
    {
        abort_unless(auth()->user()?->can('manage_sale_returns'), 403);
    }

    public function render()
    {
        return view('livewire.sale-returns.sale-return-management');
    }
}
