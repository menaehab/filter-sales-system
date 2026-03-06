<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('supplier_details')]
class SupplierDetails extends Component
{
    public Supplier $supplier;

    public function mount(Supplier $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function getStatusColorProperty(): string
    {
        return filled($this->supplier->phone) ? 'emerald' : 'yellow';
    }

    public function getStatusLabelProperty(): string
    {
        return filled($this->supplier->phone)
            ? __('keywords.contact_available')
            : __('keywords.contact_missing');
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-details', [
            'supplier' => $this->supplier,
        ]);
    }
}
