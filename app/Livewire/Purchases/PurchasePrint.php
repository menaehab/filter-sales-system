<?php

namespace App\Livewire\Purchases;

use App\Models\Purchase;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.print')]
class PurchasePrint extends Component
{
    public Purchase $purchase;

    public function mount(Purchase $purchase)
    {
        $this->purchase = $purchase->load(['items.product', 'supplier', 'paymentAllocations']);
    }

    public function render()
    {
        return view('livewire.purchases.purchase-print');
    }
}
