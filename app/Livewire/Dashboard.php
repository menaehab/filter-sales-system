<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'dashboard'])]
class Dashboard extends Component
{
    public function getTotalProductsProperty(): int
    {
        return Product::count();
    }

    public function getTotalCategoriesProperty(): int
    {
        return Category::count();
    }

    public function getTotalCustomersProperty(): int
    {
        return Customer::count();
    }

    public function getTotalSuppliersProperty(): int
    {
        return Supplier::count();
    }

    public function getTotalStockProperty(): int
    {
        return (int) Product::sum('quantity');
    }

    public function getLowStockCountProperty(): int
    {
        return Product::where('quantity', '<=', 5)->count();
    }

    public function getLowStockProductsProperty()
    {
        return Product::with('category')
            ->where('quantity', '<=', 5)
            ->orderBy('quantity')
            ->limit(5)
            ->get();
    }

    public function getRecentProductsProperty()
    {
        return Product::with('category')
            ->latest()
            ->limit(5)
            ->get();
    }

    public function getTopCategoriesProperty()
    {
        return Category::withCount('products')
            ->orderByDesc('products_count')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
