<?php

namespace App\Livewire\Customers;

use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Customer;
use App\Models\SaleReturn;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CustomerView extends Component
{
    use WithSearchAndPagination;

    public Customer $customer;
    public string $activeTab = 'sales';

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getSalesProperty()
    {
        return $this->customer->sales()
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getPaymentsProperty()
    {
        return $this->customer->payments()
            ->with(['user', 'allocations.sale'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getReturnsProperty()
    {
        // Get sale returns for this customer's sales where cash_refund = false.
        return SaleReturn::whereIn('sale_id', $this->customer->sales()->pluck('id'))
            ->where('cash_refund', false)
            ->with('user', 'sale')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.customers.customer-view', [
            'sales' => $this->sales,
            'payments' => $this->payments,
            'returns' => $this->returns,
        ]);
    }
}
