<?php

namespace App\Livewire\SupplierPayments;

use App\Actions\SupplierPayments\DeleteSupplierPaymentAction;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\SupplierPayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupplierPaymentManagement extends Component
{
    use HasCrudModals, HasCrudQuery, WithSearchAndPagination;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    protected function getModelClass(): string
    {
        return SupplierPayment::class;
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    protected function getSearchableFields(): array
    {
        return ['supplier.name', 'user.name', 'payment_method', 'note', 'allocations.purchase.number'];
    }

    protected function getWithRelations(): array
    {
        return ['supplier', 'user', 'allocations.purchase'];
    }

    #[Computed]
    public function supplierPayments(): LengthAwarePaginator
    {
        return $this->items;
    }

    public function setDelete(int $id): void
    {
        $this->authorizeManageSupplierPayments();
        $this->deleteId = $id;
        $this->dispatch('open-modal-delete-supplier-payment');
    }

    public function delete(DeleteSupplierPaymentAction $action): void
    {
        $this->authorizeManageSupplierPayments();

        $payment = SupplierPayment::find($this->deleteId);

        if ($payment) {
            $action->execute($payment);
        }

        $this->deleteId = null;
        $this->dispatch('close-modal-delete-supplier-payment');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.supplier-payments.supplier-payment-management');
    }

    private function authorizeManageSupplierPayments(): void
    {
        abort_unless(auth()->user()->can('manage_supplier_payment_allocations'), 403);
    }
}
