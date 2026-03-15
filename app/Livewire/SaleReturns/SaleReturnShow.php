<?php

namespace App\Livewire\SaleReturns;

use App\Models\SaleReturn;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleReturnShow extends Component
{
    public SaleReturn $saleReturn;

    public function mount(SaleReturn $saleReturn): void
    {
        $this->saleReturn = $saleReturn->load([
            'sale.customer',
            'items.product',
            'user',
        ]);
    }

    public function render()
    {
        return view('livewire.sale-returns.sale-return-show', [
            'saleReturn' => $this->saleReturn,
        ]);
    }
}
