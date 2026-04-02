<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\DamagedProduct;
use App\Models\Expense;
use App\Models\Maintenance;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * Get total sales amount
     */
    public function getTotalSales(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = Sale::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return (float) $query->sum('total_price');
    }

    /**
     * Get total purchases amount
     */
    public function getTotalPurchases(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = Purchase::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return (float) $query->sum('total_price');
    }

    /**
     * Get total expenses amount (direct expenses only)
     */
    public function getTotalExpenses(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = Expense::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

          return (float) $query->sum('amount');
    }

    /**
      * Get total maintenance revenue
     */
    public function getTotalMaintenanceCosts(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = Maintenance::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return (float) $query->sum('cost');
    }

    /**
     * Get total sale returns amount
     */
    public function getTotalSaleReturns(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = SaleReturn::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return (float) $query->sum('total_price');
    }

    /**
     * Get total purchase returns amount
     */
    public function getTotalPurchaseReturns(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = PurchaseReturn::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return (float) $query->sum('total_price');
    }

    /**
     * Get total damaged products cost
     */
    public function getTotalDamagedProductsCost(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = DamagedProduct::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return (float) $query->selectRaw('SUM(cost_price * quantity) as total')
            ->value('total') ?? 0;
    }

    /**
     * Calculate net profit using the exact business logic:
     * Net Profit = Sales + MaintenanceRevenue + PurchaseReturns - Purchases - SaleReturns - DamagedProducts - Expenses
     */
    public function getNetProfit(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $sales = $this->getTotalSales($dateFrom, $dateTo);
        $maintenanceRevenue = $this->getTotalMaintenanceCosts($dateFrom, $dateTo);
        $purchases = $this->getTotalPurchases($dateFrom, $dateTo);
        $expenses = $this->getTotalExpenses($dateFrom, $dateTo);
        $saleReturns = $this->getTotalSaleReturns($dateFrom, $dateTo);
        $purchaseReturns = $this->getTotalPurchaseReturns($dateFrom, $dateTo);
        $damagedProducts = $this->getTotalDamagedProductsCost($dateFrom, $dateTo);

        return $sales + $maintenanceRevenue + $purchaseReturns - $purchases - $saleReturns - $damagedProducts - $expenses;
    }

    /**
     * Get total customer payments
     */
    public function getTotalCustomerPayments(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = CustomerPayment::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Get total supplier payments
     */
    public function getTotalSupplierPayments(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $query = SupplierPayment::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Get outstanding customer balances
     */
    public function getOutstandingCustomerBalances(): float
    {
        return (float) Customer::query()
            ->get()
            ->sum(fn (Customer $customer) => (float) $customer->balance);
    }

    /**
     * Get outstanding supplier balances
     */
    public function getOutstandingSupplierBalances(): float
    {
        return (float) Supplier::query()
            ->get()
            ->sum(fn (Supplier $supplier) => (float) $supplier->balance);
    }

    /**
     * Get cash flow data (incoming vs outgoing)
     */
    public function getCashFlow(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $incoming = $this->getTotalCustomerPayments($dateFrom, $dateTo)
                  + $this->getTotalMaintenanceCosts($dateFrom, $dateTo);
        $outgoing = $this->getTotalSupplierPayments($dateFrom, $dateTo)
                  + $this->getTotalExpenses($dateFrom, $dateTo);

        return [
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'net' => $incoming - $outgoing,
        ];
    }

    /**
     * Get sales over time (grouped by date)
     */
    public function getSalesOverTime(?string $dateFrom = null, ?string $dateTo = null, string $groupBy = 'day'): array
    {
        $query = Sale::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $dateFormat = match ($groupBy) {
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };

        return $query->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as date, SUM(total_price) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'total' => (float) $item->total,
            ])
            ->toArray();
    }

    /**
     * Get profit over time
     */
    public function getProfitOverTime(?string $dateFrom = null, ?string $dateTo = null, string $groupBy = 'day'): array
    {
        $dateFormat = match ($groupBy) {
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };

        // Get sales by date
        $salesQuery = Sale::query();
        if ($dateFrom) {
            $salesQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->whereDate('created_at', '<=', $dateTo);
        }
        $sales = $salesQuery->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as date, SUM(total_price) as total")
            ->groupBy('date')
            ->pluck('total', 'date');

        // Get purchases by date
        $purchasesQuery = Purchase::query();
        if ($dateFrom) {
            $purchasesQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $purchasesQuery->whereDate('created_at', '<=', $dateTo);
        }
        $purchases = $purchasesQuery->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as date, SUM(total_price) as total")
            ->groupBy('date')
            ->pluck('total', 'date');

        // Get expenses by date
        $expensesQuery = Expense::query();
        if ($dateFrom) {
            $expensesQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $expensesQuery->whereDate('created_at', '<=', $dateTo);
        }
        $expenses = $expensesQuery->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as date, SUM(amount) as total")
            ->groupBy('date')
            ->pluck('total', 'date');

        // Get maintenance costs by date
        $maintenanceQuery = Maintenance::query();
        if ($dateFrom) {
            $maintenanceQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $maintenanceQuery->whereDate('created_at', '<=', $dateTo);
        }
        $maintenanceCosts = $maintenanceQuery->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as date, SUM(cost) as total")
            ->groupBy('date')
            ->pluck('total', 'date');

        // Combine all dates
        $allDates = collect($sales->keys())
            ->merge($purchases->keys())
            ->merge($expenses->keys())
            ->merge($maintenanceCosts->keys())
            ->unique()
            ->sort()
            ->values();

        return $allDates->map(function ($date) use ($sales, $purchases, $expenses, $maintenanceCosts) {
            $saleAmount = $sales->get($date, 0);
            $purchaseAmount = $purchases->get($date, 0);
            $expenseAmount = $expenses->get($date, 0);
            $maintenanceAmount = $maintenanceCosts->get($date, 0);
            $profit = $saleAmount - $purchaseAmount - $expenseAmount + $maintenanceAmount;

            return [
                'date' => $date,
                'profit' => (float) $profit,
            ];
        })->toArray();
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts(int $limit = 10, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.sell_price * sale_items.quantity) as total_revenue')
            )
            ->groupBy('products.id', 'products.name');

        if ($dateFrom) {
            $query->whereDate('sales.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('sales.created_at', '<=', $dateTo);
        }

        return $query->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'total_quantity' => (float) $item->total_quantity,
                'total_revenue' => (float) $item->total_revenue,
            ])
            ->toArray();
    }

    /**
     * Get most purchased products
     */
    public function getMostPurchasedProducts(int $limit = 10, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = DB::table('purchase_items')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(purchase_items.quantity) as total_quantity'),
                DB::raw('SUM(purchase_items.cost_price * purchase_items.quantity) as total_cost')
            )
            ->groupBy('products.id', 'products.name');

        if ($dateFrom) {
            $query->whereDate('purchases.created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('purchases.created_at', '<=', $dateTo);
        }

        return $query->orderByDesc('total_quantity')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'total_quantity' => (float) $item->total_quantity,
                'total_cost' => (float) $item->total_cost,
            ])
            ->toArray();
    }

    /**
     * Get return statistics
     */
    public function getReturnStatistics(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $saleReturnsQuery = SaleReturn::query();
        $purchaseReturnsQuery = PurchaseReturn::query();

        if ($dateFrom) {
            $saleReturnsQuery->whereDate('created_at', '>=', $dateFrom);
            $purchaseReturnsQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $saleReturnsQuery->whereDate('created_at', '<=', $dateTo);
            $purchaseReturnsQuery->whereDate('created_at', '<=', $dateTo);
        }

        return [
            'sale_returns' => [
                'count' => $saleReturnsQuery->count(),
                'total' => (float) $saleReturnsQuery->sum('total_price'),
            ],
            'purchase_returns' => [
                'count' => $purchaseReturnsQuery->count(),
                'total' => (float) $purchaseReturnsQuery->sum('total_price'),
            ],
        ];
    }

    /**
     * Get damage statistics
     */
    public function getDamageStatistics(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = DamagedProduct::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return [
            'count' => $query->count(),
            'total_quantity' => (float) $query->sum('quantity'),
            'total_cost' => (float) $query->selectRaw('SUM(cost_price * quantity) as total')->value('total') ?? 0,
        ];
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStatistics(?string $dateFrom = null, ?string $dateTo = null): array
    {
        // Customer payments
        $customerPaymentsQuery = CustomerPayment::query();
        if ($dateFrom) {
            $customerPaymentsQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $customerPaymentsQuery->whereDate('created_at', '<=', $dateTo);
        }

        // Supplier payments
        $supplierPaymentsQuery = SupplierPayment::query();
        if ($dateFrom) {
            $supplierPaymentsQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $supplierPaymentsQuery->whereDate('created_at', '<=', $dateTo);
        }

        return [
            'customer_payments' => [
                'count' => $customerPaymentsQuery->count(),
                'total' => (float) $customerPaymentsQuery->sum('amount'),
            ],
            'supplier_payments' => [
                'count' => $supplierPaymentsQuery->count(),
                'total' => (float) $supplierPaymentsQuery->sum('amount'),
            ],
        ];
    }

    /**
     * Get revenue vs expenses data for chart
     */
    public function getRevenueVsExpenses(?string $dateFrom = null, ?string $dateTo = null): array
    {
        return [
            'revenue' => $this->getTotalSales($dateFrom, $dateTo) + $this->getTotalMaintenanceCosts($dateFrom, $dateTo),
            'expenses' => $this->getTotalExpenses($dateFrom, $dateTo) + $this->getTotalPurchases($dateFrom, $dateTo),
        ];
    }

    /**
     * Get customer with highest balances
     */
    public function getTopCustomersByBalance(int $limit = 10): array
    {
        return Customer::query()
            ->get(['id', 'name'])
            ->filter(fn (Customer $customer) => (float) $customer->balance > 0)
            ->sortByDesc(fn (Customer $customer) => (float) $customer->balance)
            ->take($limit)
            ->values()
            ->map(fn ($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'balance' => (float) $customer->balance,
            ])
            ->toArray();
    }

    /**
     * Get suppliers with highest balances
     */
    public function getTopSuppliersByBalance(int $limit = 10): array
    {
        return Supplier::query()
            ->get(['id', 'name'])
            ->filter(fn (Supplier $supplier) => (float) $supplier->balance > 0)
            ->sortByDesc(fn (Supplier $supplier) => (float) $supplier->balance)
            ->take($limit)
            ->values()
            ->map(fn ($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'balance' => (float) $supplier->balance,
            ])
            ->toArray();
    }

    /**
     * Get invoice counts
     */
    public function getInvoiceCounts(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $salesQuery = Sale::query();
        $purchasesQuery = Purchase::query();

        if ($dateFrom) {
            $salesQuery->whereDate('created_at', '>=', $dateFrom);
            $purchasesQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->whereDate('created_at', '<=', $dateTo);
            $purchasesQuery->whereDate('created_at', '<=', $dateTo);
        }

        return [
            'sales' => $salesQuery->count(),
            'purchases' => $purchasesQuery->count(),
        ];
    }
}
