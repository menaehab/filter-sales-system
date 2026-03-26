<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StatisticsService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'dashboard'])]
class Dashboard extends Component
{
    protected StatisticsService $statsService;

    #[Url(as: 'from', except: '')]
    public ?string $dateFrom = null;

    #[Url(as: 'to', except: '')]
    public ?string $dateTo = null;

    public string $chartPeriod = 'day';

    public function boot(StatisticsService $statsService): void
    {
        $this->statsService = $statsService;
    }

    #[Computed]
    public function totalSales(): float
    {
        return $this->statsService->getTotalSales($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function totalPurchases(): float
    {
        return $this->statsService->getTotalPurchases($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function totalExpenses(): float
    {
        return $this->statsService->getTotalExpenses($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function netProfit(): float
    {
        return $this->statsService->getNetProfit($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function totalCustomerPayments(): float
    {
        return $this->statsService->getTotalCustomerPayments($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function totalSupplierPayments(): float
    {
        return $this->statsService->getTotalSupplierPayments($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function outstandingCustomerBalances(): float
    {
        return $this->statsService->getOutstandingCustomerBalances();
    }

    #[Computed]
    public function outstandingSupplierBalances(): float
    {
        return $this->statsService->getOutstandingSupplierBalances();
    }

    #[Computed]
    public function cashFlow(): array
    {
        return $this->statsService->getCashFlow($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function salesOverTime(): array
    {
        return $this->statsService->getSalesOverTime($this->dateFrom, $this->dateTo, $this->chartPeriod);
    }

    #[Computed]
    public function profitOverTime(): array
    {
        return $this->statsService->getProfitOverTime($this->dateFrom, $this->dateTo, $this->chartPeriod);
    }

    #[Computed]
    public function topSellingProducts(): array
    {
        return $this->statsService->getTopSellingProducts(10, $this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function mostPurchasedProducts(): array
    {
        return $this->statsService->getMostPurchasedProducts(10, $this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function returnStatistics(): array
    {
        return $this->statsService->getReturnStatistics($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function damageStatistics(): array
    {
        return $this->statsService->getDamageStatistics($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function paymentStatistics(): array
    {
        return $this->statsService->getPaymentStatistics($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function revenueVsExpenses(): array
    {
        return $this->statsService->getRevenueVsExpenses($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function topCustomersByBalance(): array
    {
        return $this->statsService->getTopCustomersByBalance(5);
    }

    #[Computed]
    public function topSuppliersByBalance(): array
    {
        return $this->statsService->getTopSuppliersByBalance(5);
    }

    #[Computed]
    public function invoiceCounts(): array
    {
        return $this->statsService->getInvoiceCounts($this->dateFrom, $this->dateTo);
    }

    #[Computed]
    public function totalProducts(): int
    {
        return Product::count();
    }

    #[Computed]
    public function totalCategories(): int
    {
        return Category::count();
    }

    #[Computed]
    public function totalCustomers(): int
    {
        return Customer::count();
    }

    #[Computed]
    public function totalSuppliers(): int
    {
        return Supplier::count();
    }

    #[Computed]
    public function totalStock(): int
    {
        return (int) Product::sum('quantity');
    }

    #[Computed]
    public function lowStockCount(): int
    {
        return Product::where('quantity', '<=', 5)->count();
    }

    #[Computed]
    public function lowStockProducts(): Collection
    {
        return Product::with('category')
            ->where('quantity', '<=', 5)
            ->orderBy('quantity')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function recentProducts(): Collection
    {
        return Product::with('category')
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function topCategories(): Collection
    {
        return Category::withCount('products')
            ->orderByDesc('products_count')
            ->limit(5)
            ->get();
    }

    public function setChartPeriod(string $period): void
    {
        $this->chartPeriod = $period;
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
