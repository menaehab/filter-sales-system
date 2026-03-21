<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.print')]
class SalePrint extends Component
{
    public Sale $sale;

    public function mount(Sale $sale)
    {
        $this->sale = $sale->load(['items.product', 'customer', 'paymentAllocations']);
    }

    public function render()
    {
        return view('livewire.sales.sale-print');
    }
}
