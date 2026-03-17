<div>
    {{-- Welcome Header with Filters --}}
    <div class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ __('keywords.welcome_back') }}, {{ auth()->user()->name }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('keywords.dashboard_description') }}</p>
            </div>

            {{-- Date Filters --}}
            <div class="flex flex-wrap gap-2">
                <x-input
                    type="date"
                    name="dateFrom"
                    wire:model.live="dateFrom"
                    placeholder="{{ __('keywords.from_date') }}"
                    class="w-full sm:w-auto"
                />
                <x-input
                    type="date"
                    name="dateTo"
                    wire:model.live="dateTo"
                    placeholder="{{ __('keywords.to_date') }}"
                    class="w-full sm:w-auto"
                />
                @if($dateFrom || $dateTo)
                    <x-button
                        wire:click="$set('dateFrom', null); $set('dateTo', null)"
                        color="secondary"
                        size="sm"
                    >
                        <i class="fas fa-times"></i>
                        {{ __('keywords.clear') }}
                    </x-button>
                @endif
            </div>
        </div>
    </div>

    {{-- Financial KPIs --}}
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card
            :label="__('keywords.total_sales')"
            :value="number_format($this->totalSales, 2)"
            iconClass="fas fa-dollar-sign"
            color="emerald"
            :suffix="__('keywords.currency')"
        />
        <x-stat-card
            :label="__('keywords.total_purchases')"
            :value="number_format($this->totalPurchases, 2)"
            iconClass="fas fa-shopping-cart"
            color="blue"
            :suffix="__('keywords.currency')"
        />
        <x-stat-card
            :label="__('keywords.total_expenses')"
            :value="number_format($this->totalExpenses, 2)"
            iconClass="fas fa-receipt"
            color="amber"
            :suffix="__('keywords.currency')"
        />
        <x-stat-card
            :label="__('keywords.net_profit')"
            :value="number_format($this->netProfit, 2)"
            iconClass="fas fa-chart-line"
            :color="$this->netProfit >= 0 ? 'emerald' : 'rose'"
            :suffix="__('keywords.currency')"
        />
    </div>

    {{-- Cash Flow & Balances --}}
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card
            :label="__('keywords.customer_payments')"
            :value="number_format($this->totalCustomerPayments, 2)"
            iconClass="fas fa-hand-holding-dollar"
            color="teal"
            :suffix="__('keywords.currency')"
        />
        <x-stat-card
            :label="__('keywords.supplier_payments')"
            :value="number_format($this->totalSupplierPayments, 2)"
            iconClass="fas fa-money-bill-transfer"
            color="violet"
            :suffix="__('keywords.currency')"
        />
        <x-stat-card
            :label="__('keywords.customer_balances')"
            :value="number_format($this->outstandingCustomerBalances, 2)"
            iconClass="fas fa-users"
            color="sky"
            :suffix="__('keywords.currency')"
        />
        <x-stat-card
            :label="__('keywords.supplier_balances')"
            :value="number_format($this->outstandingSupplierBalances, 2)"
            iconClass="fas fa-truck"
            color="indigo"
            :suffix="__('keywords.currency')"
        />
    </div>

    {{-- Returns & Damages Stats --}}
    @php
        $returnStats = $this->returnStatistics;
        $damageStats = $this->damageStatistics;
        $invoiceCounts = $this->invoiceCounts;
    @endphp
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card
            :label="__('keywords.sale_returns')"
            :value="$returnStats['sale_returns']['count'] . ' (' . number_format($returnStats['sale_returns']['total'], 2) . ')'"
            iconClass="fas fa-undo"
            color="rose"
        />
        <x-stat-card
            :label="__('keywords.purchase_returns')"
            :value="$returnStats['purchase_returns']['count'] . ' (' . number_format($returnStats['purchase_returns']['total'], 2) . ')'"
            iconClass="fas fa-rotate-left"
            color="orange"
        />
        <x-stat-card
            :label="__('keywords.damaged_products')"
            :value="$damageStats['count'] . ' (' . number_format($damageStats['total_cost'], 2) . ')'"
            iconClass="fas fa-box-open"
            color="red"
        />
        <x-stat-card
            :label="__('keywords.total_invoices')"
            :value="$invoiceCounts['sales'] + $invoiceCounts['purchases']"
            iconClass="fas fa-file-invoice"
            color="purple"
        />
    </div>

    {{-- Chart Period Selector --}}
    <div class="mb-4 flex justify-end gap-2">
        <button
            wire:click="setChartPeriod('day')"
            class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ $chartPeriod === 'day' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
        >
            {{ __('keywords.daily') }}
        </button>
        <button
            wire:click="setChartPeriod('month')"
            class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ $chartPeriod === 'month' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
        >
            {{ __('keywords.monthly') }}
        </button>
    </div>

    {{-- Charts Grid --}}
    <div class="mb-8 grid grid-cols-1 gap-6 xl:grid-cols-2">
        {{-- Sales Over Time Chart --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ __('keywords.sales_over_time') }}</h3>
            <canvas id="salesChart" wire:ignore></canvas>
        </div>

        {{-- Profit Over Time Chart --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ __('keywords.profit_over_time') }}</h3>
            <canvas id="profitChart" wire:ignore></canvas>
        </div>

        {{-- Top Selling Products Chart --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ __('keywords.top_selling_products') }}</h3>
            <canvas id="topProductsChart" wire:ignore></canvas>
        </div>

        {{-- Revenue vs Expenses Pie --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ __('keywords.revenue_vs_expenses') }}</h3>
            <canvas id="revenueExpensesChart" wire:ignore></canvas>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-4">
        {{-- Low Stock Alerts --}}
        <div class="xl:col-span-1">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-triangle-exclamation text-amber-500"></i>
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.low_stock_alerts') }}</h3>
                    </div>
                    @if($this->lowStockCount > 0)
                        <x-badge :label="$this->lowStockCount . ' ' . __('keywords.items')" color="red" />
                    @endif
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($this->lowStockProducts as $product)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->category?->name }}</p>
                            </div>
                            <x-badge
                                :label="$product->quantity . ' ' . __('keywords.in_stock')"
                                :color="$product->quantity === 0 ? 'red' : 'yellow'"
                            />
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <i class="fas fa-check-circle mb-2 text-2xl text-emerald-400"></i>
                            <p class="text-sm text-gray-500">{{ __('keywords.all_stocked') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Customers by Balance --}}
        <div class="xl:col-span-1">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-tie text-sky-500"></i>
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.top_customer_debts') }}</h3>
                    </div>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($this->topCustomersByBalance as $customer)
                        <div class="flex items-center justify-between px-5 py-3">
                            <p class="truncate text-sm font-medium text-gray-900">{{ $customer['name'] }}</p>
                            <span class="text-sm font-semibold text-rose-600">{{ number_format($customer['balance'], 2) }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <i class="fas fa-check-circle mb-2 text-2xl text-emerald-400"></i>
                            <p class="text-sm text-gray-500">{{ __('keywords.no_debts') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Suppliers by Balance --}}
        <div class="xl:col-span-1">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-truck-field text-indigo-500"></i>
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.top_supplier_debts') }}</h3>
                    </div>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($this->topSuppliersByBalance as $supplier)
                        <div class="flex items-center justify-between px-5 py-3">
                            <p class="truncate text-sm font-medium text-gray-900">{{ $supplier['name'] }}</p>
                            <span class="text-sm font-semibold text-rose-600">{{ number_format($supplier['balance'], 2) }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <i class="fas fa-check-circle mb-2 text-2xl text-emerald-400"></i>
                            <p class="text-sm text-gray-500">{{ __('keywords.no_debts') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Categories --}}
        <div class="xl:col-span-1">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chart-pie text-violet-500"></i>
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.top_categories') }}</h3>
                    </div>
                    @can('manage_categories')
                        <a href="{{ route('categories') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                            {{ __('keywords.view_all') }}
                        </a>
                    @endcan
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($this->topCategories as $category)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50">
                                    <i class="fas fa-tag text-xs text-emerald-600"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-900">{{ $category->name }}</p>
                            </div>
                            <x-badge
                                :label="$category->products_count . ' ' . __('keywords.products')"
                                color="blue"
                            />
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <p class="text-sm text-gray-500">{{ __('keywords.no_categories_found') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:navigated', function () {
        initCharts();
    });

    function initCharts() {
        // Sales Over Time Chart
        const salesData = @json($this->salesOverTime);
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: salesData.map(item => item.date),
                    datasets: [{
                        label: '{{ __("keywords.sales") }}',
                        data: salesData.map(item => item.total),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Profit Over Time Chart
        const profitData = @json($this->profitOverTime);
        const profitCtx = document.getElementById('profitChart');
        if (profitCtx) {
            new Chart(profitCtx, {
                type: 'line',
                data: {
                    labels: profitData.map(item => item.date),
                    datasets: [{
                        label: '{{ __("keywords.profit") }}',
                        data: profitData.map(item => item.profit),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        }

        // Top Selling Products Chart
        const topProducts = @json($this->topSellingProducts);
        const topProductsCtx = document.getElementById('topProductsChart');
        if (topProductsCtx) {
            new Chart(topProductsCtx, {
                type: 'bar',
                data: {
                    labels: topProducts.map(item => item.name),
                    datasets: [{
                        label: '{{ __("keywords.quantity") }}',
                        data: topProducts.map(item => item.total_quantity),
                        backgroundColor: 'rgba(139, 92, 246, 0.7)',
                        borderColor: 'rgb(139, 92, 246)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Revenue vs Expenses Pie Chart
        const revenueExpenses = @json($this->revenueVsExpenses);
        const revenueExpensesCtx = document.getElementById('revenueExpensesChart');
        if (revenueExpensesCtx) {
            new Chart(revenueExpensesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['{{ __("keywords.revenue") }}', '{{ __("keywords.expenses") }}'],
                    datasets: [{
                        data: [revenueExpenses.revenue, revenueExpenses.expenses],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.7)',
                            'rgba(239, 68, 68, 0.7)'
                        ],
                        borderColor: [
                            'rgb(16, 185, 129)',
                            'rgb(239, 68, 68)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }

    // Initialize charts on first load
    if (document.readyState === 'complete') {
        initCharts();
    } else {
        window.addEventListener('load', initCharts);
    }

    // Reinitialize charts when filters change
    Livewire.hook('morph.updated', ({el, component}) => {
        if (component.name === 'dashboard') {
            // Destroy existing charts
            Chart.helpers.each(Chart.instances, function(instance) {
                instance.destroy();
            });
            // Reinitialize
            setTimeout(initCharts, 100);
        }
    });
</script>
@endpush

