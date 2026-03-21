<?php

namespace App\Livewire\Sales;

use App\Models\CustomerPayment;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.print')]
class PaymentReceiptPrint extends Component
{
    public CustomerPayment $payment;

    public function mount(CustomerPayment $payment)
    {
        $this->payment = $payment->load(['customer', 'allocations.sale']);
    }

    public function render()
    {
        return view('livewire.sales.payment-receipt-print');
    }
}
