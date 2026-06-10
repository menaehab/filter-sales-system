<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Place;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.print', ['title' => 'قائمة العملاء'])]
class CustomerPrint extends Component
{
    public function render(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $placesParam = (string) $request->query('places', '');
        $placeIds = array_filter(array_map('intval', explode(',', $placesParam)));

        $customers = Customer::query()
            ->with(['place', 'phones', 'sales', 'payments'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('national_number', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhereHas('place', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('phones', fn ($p) => $p->where('number', 'like', "%{$search}%"));
                });
            })
            ->when($placeIds !== [], fn ($query) => $query->whereIn('place_id', $placeIds))
            ->byUserPlaces()
            ->orderBy('code', 'asc')
            ->get();

        return view('livewire.customers.customer-print', compact('customers'));
    }
}
