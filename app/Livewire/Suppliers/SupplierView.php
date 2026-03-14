<?php

namespace App\Livewire\Suppliers;

use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupplierView extends Component
{
    use WithSearchAndPagination;

    public Supplier $supplier;
    public string $activeTab = 'purchases';

    public function mount(Supplier $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getPurchasesProperty()
    {
        return $this->supplier->purchases()
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getPaymentsProperty()
    {
        return $this->supplier->payments()
            ->with(['user', 'allocations.purchase'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getReturnsProperty()
    {
        // Get purchase returns for this supplier's purchases where cash_refund = false
        return PurchaseReturn::whereIn('purchase_id', $this->supplier->purchases()->pluck('id'))
            ->where('cash_refund', false)
            ->with('user', 'purchase')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-view', [
            'purchases' => $this->purchases,
            'payments' => $this->payments,
            'returns' => $this->returns,
        ]);
    }
}
