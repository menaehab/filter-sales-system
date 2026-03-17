<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StatisticsService;
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

    public function boot(StatisticsService $statsService)
    {
        $this->statsService = $statsService;
    }

    // Main KPIs
    public function getTotalSalesProperty(): float
    {
        return $this->statsService->getTotalSales($this->dateFrom, $this->dateTo);
    }

    public function getTotalPurchasesProperty(): float
    {
        return $this->statsService->getTotalPurchases($this->dateFrom, $this->dateTo);
    }

    public function getTotalExpensesProperty(): float
    {
        return $this->statsService->getTotalExpenses($this->dateFrom, $this->dateTo);
    }

    public function getNetProfitProperty(): float
    {
        return $this->statsService->getNetProfit($this->dateFrom, $this->dateTo);
    }

    public function getTotalCustomerPaymentsProperty(): float
    {
        return $this->statsService->getTotalCustomerPayments($this->dateFrom, $this->dateTo);
    }

    public function getTotalSupplierPaymentsProperty(): float
    {
        return $this->statsService->getTotalSupplierPayments($this->dateFrom, $this->dateTo);
    }

    public function getOutstandingCustomerBalancesProperty(): float
    {
        return $this->statsService->getOutstandingCustomerBalances();
    }

    public function getOutstandingSupplierBalancesProperty(): float
    {
        return $this->statsService->getOutstandingSupplierBalances();
    }

    // Cash Flow
    public function getCashFlowProperty(): array
    {
        return $this->statsService->getCashFlow($this->dateFrom, $this->dateTo);
    }

    // Charts Data
    public function getSalesOverTimeProperty(): array
    {
        return $this->statsService->getSalesOverTime($this->dateFrom, $this->dateTo, $this->chartPeriod);
    }

    public function getProfitOverTimeProperty(): array
    {
        return $this->statsService->getProfitOverTime($this->dateFrom, $this->dateTo, $this->chartPeriod);
    }

    public function getTopSellingProductsProperty(): array
    {
        return $this->statsService->getTopSellingProducts(10, $this->dateFrom, $this->dateTo);
    }

    public function getMostPurchasedProductsProperty(): array
    {
        return $this->statsService->getMostPurchasedProducts(10, $this->dateFrom, $this->dateTo);
    }

    // Return & Damage Statistics
    public function getReturnStatisticsProperty(): array
    {
        return $this->statsService->getReturnStatistics($this->dateFrom, $this->dateTo);
    }

    public function getDamageStatisticsProperty(): array
    {
        return $this->statsService->getDamageStatistics($this->dateFrom, $this->dateTo);
    }

    // Payment Statistics
    public function getPaymentStatisticsProperty(): array
    {
        return $this->statsService->getPaymentStatistics($this->dateFrom, $this->dateTo);
    }

    // Revenue vs Expenses
    public function getRevenueVsExpensesProperty(): array
    {
        return $this->statsService->getRevenueVsExpenses($this->dateFrom, $this->dateTo);
    }

    // Top Customers & Suppliers
    public function getTopCustomersByBalanceProperty(): array
    {
        return $this->statsService->getTopCustomersByBalance(5);
    }

    public function getTopSuppliersByBalanceProperty(): array
    {
        return $this->statsService->getTopSuppliersByBalance(5);
    }

    // Invoice Counts
    public function getInvoiceCountsProperty(): array
    {
        return $this->statsService->getInvoiceCounts($this->dateFrom, $this->dateTo);
    }

    // Existing Properties
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

    public function setChartPeriod(string $period)
    {
        $this->chartPeriod = $period;
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
