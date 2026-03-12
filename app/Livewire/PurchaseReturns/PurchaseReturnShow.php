<?php

namespace App\Livewire\PurchaseReturns;

use App\Models\PurchaseReturn;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class PurchaseReturnShow extends Component
{
    public PurchaseReturn $purchaseReturn;

    public function mount(PurchaseReturn $purchaseReturn): void
    {
        $this->purchaseReturn = $purchaseReturn->load([
            'purchase',
            'items.product',
            'user',
        ]);
    }

    public function render()
    {
        return view('livewire.purchase-returns.purchase-return-show', [
            'purchaseReturn' => $this->purchaseReturn,
        ]);
    }
}
