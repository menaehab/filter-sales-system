<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseShow extends Component
{
    public Purchase $purchase;

    public function mount(Purchase $purchase): void
    {
        $this->purchase = $purchase->load([
            'supplier',
            'user',
            'items.product',
            'paymentAllocations.supplierPayment',
        ]);
    }

    public function render()
    {
        return view('livewire.purchases.purchase-show', [
            'purchase' => $this->purchase,
        ]);
    }
}
