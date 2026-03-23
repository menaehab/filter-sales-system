<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'customer_details'])]
class CustomerDetails extends Component
{
    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function getStatusColorProperty(): string
    {
        return filled($this->customer->phone)
            ? 'emerald'
            : 'yellow';
    }

    public function getStatusLabelProperty(): string
    {
        return filled($this->customer->phone)
            ? __('keywords.contact_available')
            : __('keywords.contact_missing');
    }

    public function render()
    {
        return view('livewire.customers.customer-details', [
            'customer' => $this->customer,
        ]);
    }
}
