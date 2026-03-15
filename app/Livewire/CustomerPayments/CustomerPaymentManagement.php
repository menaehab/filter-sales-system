<?php

namespace App\Livewire\CustomerPayments;

use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\CustomerPayment;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app',['title' => 'customer_payments'])]
class CustomerPaymentManagement extends Component
{
    use HasCrudModals, WithSearchAndPagination, HasCrudQuery;

    protected function getModelClass(): string
    {
        return CustomerPayment::class;
    }

    protected function getSearchableFields(): array
    {
        return ['customer.name', 'user.name', 'payment_method', 'note', 'allocations.purchase.number'];
    }

    protected function getWithRelations(): array
    {
        return ['customer', 'user', 'allocations.purchase'];
    }

    public function getCustomerPaymentsProperty()
    {
        return $this->items;
    }

    public function setDelete($id)
    {
        $this->authorizeManageCustomerPayments();
        $this->openDeleteModal($id, 'open-modal-delete-customer-payment');
    }

    public function delete()
    {
        $this->authorizeManageCustomerPayments();

        CustomerPayment::find($this->deleteId)?->delete();

        $this->deleteId = null;
        $this->dispatch('close-modal-delete-customer-payment');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.customer-payments.customer-payment-management');
    }

    private function authorizeManageCustomerPayments(): void
    {
        abort_unless(auth()->user()->can('manage_customer_payment_allocations'), 403);
    }
}
