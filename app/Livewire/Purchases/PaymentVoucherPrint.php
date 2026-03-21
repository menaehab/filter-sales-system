<?php

namespace App\Livewire\Purchases;

use App\Models\SupplierPayment;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.print')]
class PaymentVoucherPrint extends Component
{
    public SupplierPayment $payment;

    public function mount(SupplierPayment $payment)
    {
        $this->payment = $payment->load(['supplier', 'allocations.purchase']);
    }

    public function render()
    {
        return view('livewire.purchases.payment-voucher-print');
    }
}
