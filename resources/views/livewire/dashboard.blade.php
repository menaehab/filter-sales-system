<div class="page-enter">
    {{-- ─── Welcome Header with Filters ─────────────────────── --}}
    <div class="mb-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                @php
                    $hour = now()->hour;
                    $greeting =
                        $hour < 12
                            ? __('keywords.good_morning')
                            : ($hour < 18
                                ? __('keywords.good_afternoon')
                                : __('keywords.good_evening'));
                @endphp
                <p class="mb-0.5 text-xs font-semibold uppercase tracking-widest text-emerald-600">
                    {{ $greeting }}
                </p>
                <h1 class="text-2xl font-bold text-gray-900">
                    {{ auth()->user()->name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('keywords.dashboard_description') }}
                    <span class="text-gray-400 before:me-1.5 before:content-['·']">
                        {{ now()->translatedFormat('l، d M Y') }}
                    </span>
                </p>
            </div>

            {{-- Date Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                <x-input type="date" name="dateFrom" wire:model.lazy="dateFrom"
                    placeholder="{{ __('keywords.from_date') }}" class="w-full sm:w-auto" />
                <x-input type="date" name="dateTo" wire:model.lazy="dateTo"
                    placeholder="{{ __('keywords.to_date') }}" class="w-full sm:w-auto" />
                @if ($dateFrom || $dateTo)
                    <x-button wire:click="$set('dateFrom', null); $set('dateTo', null)" variant="secondary"
                        size="sm" icon="fas fa-xmark">
                        {{ __('keywords.clear') }}
                    </x-button>
                @endif
            </div>
        </div>
    </div>

    {{-- ─── Financial KPIs ───────────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 stagger-children"
        wire:loading.class="opacity-60 pointer-events-none">
        <x-stat-card :label="__('keywords.total_sales')" :value="number_format($this->totalSales, 2)" iconClass="fas fa-arrow-trend-up" color="emerald"
            :suffix="__('keywords.currency')" />
        <x-stat-card :label="__('keywords.total_purchases')" :value="number_format($this->totalPurchases, 2)" iconClass="fas fa-shopping-bag" color="blue"
            :suffix="__('keywords.currency')" />
        <x-stat-card :label="__('keywords.total_expenses')" :value="number_format($this->totalExpenses, 2)" iconClass="fas fa-receipt" color="amber" :suffix="__('keywords.currency')" />
        <x-stat-card :label="__('keywords.net_profit')" :value="number_format($this->netProfit, 2)" iconClass="fas fa-chart-line" :color="$this->netProfit >= 0 ? 'emerald' : 'rose'"
            :suffix="__('keywords.currency')" />
    </div>

    {{-- ─── Cash Flow & Balances ─────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5 stagger-children"
        wire:loading.class="opacity-60 pointer-events-none">
        <x-stat-card :label="__('keywords.customer_payments')" :value="number_format($this->totalCustomerPayments, 2)" iconClass="fas fa-hand-holding-dollar" color="teal"
            :suffix="__('keywords.currency')" />
        <x-stat-card :label="__('keywords.maintenance_revenue')" :value="number_format($this->totalMaintenanceRevenue, 2)" iconClass="fas fa-screwdriver-wrench" color="emerald"
            :suffix="__('keywords.currency')" />
        <x-stat-card :label="__('keywords.supplier_payments')" :value="number_format($this->totalSupplierPayments, 2)" iconClass="fas fa-money-bill-transfer" color="violet"
            :suffix="__('keywords.currency')" />
        <x-stat-card :label="__('keywords.customer_balances')" :value="number_format($this->outstandingCustomerBalances, 2)" iconClass="fas fa-users" color="sky" :suffix="__('keywords.currency')" />
        <x-stat-card :label="__('keywords.supplier_balances')" :value="number_format($this->outstandingSupplierBalances, 2)" iconClass="fas fa-truck" color="indigo" :suffix="__('keywords.currency')" />
    </div>

    {{-- ─── Returns & Damages ────────────────────────────────── --}}
    @php
        $returnStats = $this->returnStatistics;
        $damageStats = $this->damageStatistics;
        $invoiceCounts = $this->invoiceCounts;
    @endphp
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 stagger-children"
        wire:loading.class="opacity-60 pointer-events-none">
        <x-stat-card :label="__('keywords.sale_returns')" :value="$returnStats['sale_returns']['count'] .
            ' (' .
            number_format($returnStats['sale_returns']['total'], 2) .
            ')'" iconClass="fas fa-undo" color="rose" />
        <x-stat-card :label="__('keywords.purchase_returns')" :value="$returnStats['purchase_returns']['count'] .
            ' (' .
            number_format($returnStats['purchase_returns']['total'], 2) .
            ')'" iconClass="fas fa-rotate-left" color="orange" />
        <x-stat-card :label="__('keywords.damaged_products')" :value="$damageStats['count'] . ' (' . number_format($damageStats['total_cost'], 2) . ')'" iconClass="fas fa-box-open" color="red" />
        <x-stat-card :label="__('keywords.total_invoices')" :value="$invoiceCounts['sales'] + $invoiceCounts['purchases']" iconClass="fas fa-file-invoice" color="purple" />
    </div>

    {{-- ─── Chart Period Selector ────────────────────────────── --}}
    <div class="mb-4 flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">
            {{ __('keywords.data_period') ?? 'Data Period' }}
        </p>
        <div class="flex items-center gap-1 rounded-xl bg-gray-100 p-1">
            @foreach (['day' => __('keywords.daily'), 'month' => __('keywords.monthly')] as $period => $label)
                <button wire:click="setChartPeriod('{{ $period }}')"
                    class="rounded-lg px-4 py-1.5 text-sm font-medium transition-all duration-200
                           {{ $chartPeriod === $period
                               ? 'bg-white text-emerald-700 shadow-sm ring-1 ring-gray-200/80'
                               : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- ─── Charts Grid ──────────────────────────────────────── --}}
    <div class="mb-8 grid grid-cols-1 gap-6 xl:grid-cols-2">
        {{-- Sales Over Time --}}
        <div
            class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm
                    transition-shadow duration-200 hover:shadow-md">
            <h2 class="mb-1 text-sm font-semibold text-gray-900">{{ __('keywords.sales_over_time') }}</h2>
            <p class="mb-4 text-xs text-gray-400">{{ __('keywords.currency') }}</p>
            <div style="height: 220px;">
                <canvas id="salesChart" wire:ignore></canvas>
            </div>
        </div>

        {{-- Profit Over Time --}}
        <div
            class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm
                    transition-shadow duration-200 hover:shadow-md">
            <h2 class="mb-1 text-sm font-semibold text-gray-900">{{ __('keywords.profit_over_time') }}</h2>
            <p class="mb-4 text-xs text-gray-400">{{ __('keywords.currency') }}</p>
            <div style="height: 220px;">
                <canvas id="profitChart" wire:ignore></canvas>
            </div>
        </div>

        {{-- Top Selling Products --}}
        <div
            class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm
                    transition-shadow duration-200 hover:shadow-md">
            <h2 class="mb-4 text-sm font-semibold text-gray-900">{{ __('keywords.top_selling_products') }}</h2>
            <div style="height: 220px;">
                <canvas id="topProductsChart" wire:ignore></canvas>
            </div>
        </div>

        {{-- Revenue vs Expenses --}}
        <div
            class="group rounded-2xl border border-gray-200 bg-white p-6 shadow-sm
                    transition-shadow duration-200 hover:shadow-md">
            <h2 class="mb-4 text-sm font-semibold text-gray-900">{{ __('keywords.revenue_vs_expenses') }}</h2>
            <div style="height: 220px;">
                <canvas id="revenueExpensesChart" wire:ignore></canvas>
            </div>
        </div>
    </div>

    {{-- ─── Main Content Grid ────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-4">

        {{-- Low Stock Alerts --}}
        <div class="xl:col-span-1">
            <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50">
                            <i class="fas fa-triangle-exclamation text-xs text-amber-500" aria-hidden="true"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-gray-900">{{ __('keywords.low_stock_alerts') }}</h2>
                    </div>
                    @if ($this->lowStockCount > 0)
                        <x-badge :label="$this->lowStockCount . ' ' . __('keywords.items')" color="red" dot />
                    @endif
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($this->lowStockProducts as $product)
                        <div
                            class="flex items-center justify-between px-5 py-3 transition-colors duration-150 hover:bg-gray-50/70">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-xs text-gray-400">{{ $product->category?->name }}</p>
                            </div>
                            <x-badge :label="$product->quantity . ' ' . __('keywords.in_stock')" :color="$product->quantity === 0 ? 'red' : 'yellow'" :dot="true" />
                        </div>
                    @empty
                        <div class="flex flex-col items-center px-5 py-10 text-center">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50">
                                <i class="fas fa-check text-sm text-emerald-500" aria-hidden="true"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-600">{{ __('keywords.all_stocked') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Customers by Balance --}}
        <div class="xl:col-span-1">
            <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-50">
                        <i class="fas fa-user-tie text-xs text-sky-500" aria-hidden="true"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('keywords.top_customer_debts') }}</h2>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($this->topCustomersByBalance as $customer)
                        <div
                            class="flex items-center justify-between px-5 py-3 transition-colors duration-150 hover:bg-gray-50/70">
                            <p class="truncate text-sm font-medium text-gray-900">{{ $customer['name'] }}</p>
                            <span class="ms-2 shrink-0 text-sm font-bold tabular-nums text-rose-600">
                                {{ number_format($customer['balance'], 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="flex flex-col items-center px-5 py-10 text-center">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50">
                                <i class="fas fa-check text-sm text-emerald-500" aria-hidden="true"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-600">{{ __('keywords.no_debts') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Suppliers by Balance --}}
        <div class="xl:col-span-1">
            <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50">
                        <i class="fas fa-truck-field text-xs text-indigo-500" aria-hidden="true"></i>
                    </div>
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('keywords.top_supplier_debts') }}</h2>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($this->topSuppliersByBalance as $supplier)
                        <div
                            class="flex items-center justify-between px-5 py-3 transition-colors duration-150 hover:bg-gray-50/70">
                            <p class="truncate text-sm font-medium text-gray-900">{{ $supplier['name'] }}</p>
                            <span class="ms-2 shrink-0 text-sm font-bold tabular-nums text-rose-600">
                                {{ number_format($supplier['balance'], 2) }}
                            </span>
                        </div>
                    @empty
                        <div class="flex flex-col items-center px-5 py-10 text-center">
                            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50">
                                <i class="fas fa-check text-sm text-emerald-500" aria-hidden="true"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-600">{{ __('keywords.no_debts') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Categories --}}
        <div class="xl:col-span-1">
            <div class="h-full rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-50">
                            <i class="fas fa-chart-pie text-xs text-violet-500" aria-hidden="true"></i>
                        </div>
                        <h2 class="text-sm font-semibold text-gray-900">{{ __('keywords.top_categories') }}</h2>
                    </div>
                    @can('manage_categories')
                        <a href="{{ route('categories') }}"
                            class="text-xs font-semibold text-emerald-600 transition-colors hover:text-emerald-700
                                  focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 rounded">
                            {{ __('keywords.view_all') }}
                        </a>
                    @endcan
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($this->topCategories as $category)
                        <div
                            class="flex items-center justify-between px-5 py-3 transition-colors duration-150 hover:bg-gray-50/70">
                            <div class="flex min-w-0 items-center gap-2.5">
                                <div
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                                    <i class="fas fa-tag text-xs text-emerald-600" aria-hidden="true"></i>
                                </div>
                                <p class="truncate text-sm font-medium text-gray-900">{{ $category->name }}</p>
                            </div>
                            <x-badge :label="$category->products_count . ' ' . __('keywords.products')" color="blue" />
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center">
                            <p class="text-sm text-gray-400">{{ __('keywords.no_categories_found') }}</p>
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
        // ─── Chart instance registry (prevents memory leaks on filter changes) ──────
        const _chartRegistry = {};

        function _destroyChart(key) {
            if (_chartRegistry[key]) {
                _chartRegistry[key].destroy();
                delete _chartRegistry[key];
            }
        }

        // ─── Shared defaults ─────────────────────────────────────────────────────────
        function _chartDefaults(extraScaleOptions = {}) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 600,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#94a3b8',
                        bodyColor: '#f1f5f9',
                        padding: {
                            x: 14,
                            y: 10
                        },
                        cornerRadius: 10,
                        displayColors: false,
                        titleFont: {
                            size: 11
                        },
                        bodyFont: {
                            size: 13,
                            weight: '600'
                        },
                    }
                },
                scales: Object.assign({
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                size: 11
                            }
                        },
                        border: {
                            display: false
                        },
                    },
                    y: {
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: {
                                size: 11
                            }
                        },
                        border: {
                            display: false
                        },
                    }
                }, extraScaleOptions),
            };
        }

        // ─── Main init ───────────────────────────────────────────────────────────────
        function initDashboardCharts() {
            // Sales Over Time
            const salesData = @json($this->salesOverTime);
            _destroyChart('sales');
            const salesCtx = document.getElementById('salesChart');
            if (salesCtx) {
                _chartRegistry['sales'] = new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: salesData.map(i => i.date),
                        datasets: [{
                            label: '{{ __('keywords.sales') }}',
                            data: salesData.map(i => i.total),
                            borderColor: '#059669',
                            backgroundColor: 'rgba(5,150,105,0.08)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#059669',
                        }]
                    },
                    options: _chartDefaults({
                        y: {
                            beginAtZero: true
                        }
                    }),
                });
            }

            // Profit Over Time
            const profitData = @json($this->profitOverTime);
            _destroyChart('profit');
            const profitCtx = document.getElementById('profitChart');
            if (profitCtx) {
                _chartRegistry['profit'] = new Chart(profitCtx, {
                    type: 'line',
                    data: {
                        labels: profitData.map(i => i.date),
                        datasets: [{
                            label: '{{ __('keywords.profit') }}',
                            data: profitData.map(i => i.profit),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.08)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#3b82f6',
                        }]
                    },
                    options: _chartDefaults(),
                });
            }

            // Top Selling Products (horizontal bar)
            const topProducts = @json($this->topSellingProducts);
            _destroyChart('topProducts');
            const topCtx = document.getElementById('topProductsChart');
            if (topCtx) {
                _chartRegistry['topProducts'] = new Chart(topCtx, {
                    type: 'bar',
                    data: {
                        labels: topProducts.map(i => i.name),
                        datasets: [{
                            label: '{{ __('keywords.quantity') }}',
                            data: topProducts.map(i => i.total_quantity),
                            backgroundColor: 'rgba(139,92,246,0.75)',
                            borderColor: 'rgba(139,92,246,1)',
                            borderWidth: 0,
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: _chartDefaults({
                        y: {
                            beginAtZero: true
                        }
                    }),
                });
            }

            // Revenue vs Expenses (doughnut)
            const revExp = @json($this->revenueVsExpenses);
            _destroyChart('revExp');
            const revExpCtx = document.getElementById('revenueExpensesChart');
            if (revExpCtx) {
                _chartRegistry['revExp'] = new Chart(revExpCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['{{ __('keywords.revenue') }}', '{{ __('keywords.expenses') }}'],
                        datasets: [{
                            data: [revExp.revenue, revExp.expenses],
                            backgroundColor: ['rgba(5,150,105,0.80)', 'rgba(239,68,68,0.80)'],
                            borderColor: ['#059669', '#ef4444'],
                            borderWidth: 2,
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 600
                        },
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: '#64748b',
                                    font: {
                                        size: 12
                                    },
                                    padding: 16,
                                    usePointStyle: true
                                },
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleColor: '#94a3b8',
                                bodyColor: '#f1f5f9',
                                padding: {
                                    x: 14,
                                    y: 10
                                },
                                cornerRadius: 10,
                            }
                        }
                    },
                });
            }
        }

        // ─── Bootstrap ─────────────────────────────────────────────────────────────
        if (document.readyState === 'complete') {
            initDashboardCharts();
        } else {
            window.addEventListener('load', initDashboardCharts);
        }

        document.addEventListener('livewire:navigated', initDashboardCharts);

        // Reinit after Livewire morph (filter changes)
        Livewire.hook('morph.updated', ({
            component
        }) => {
            if (component.name === 'dashboard') {
                setTimeout(initDashboardCharts, 120);
            }
        });
    </script>
@endpush
