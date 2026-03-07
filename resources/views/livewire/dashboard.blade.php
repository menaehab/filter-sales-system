<div>
    {{-- Welcome Header --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">
            {{ __('keywords.welcome_back') }}, {{ auth()->user()->name }}
        </h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('keywords.dashboard_description') }}</p>
    </div>

    {{-- Stats Grid --}}
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card
            :label="__('keywords.total_products')"
            :value="$this->totalProducts"
            iconClass="fas fa-cube"
            color="emerald"
        />
        <x-stat-card
            :label="__('keywords.categories')"
            :value="$this->totalCategories"
            iconClass="fas fa-tag"
            color="sky"
        />
        <x-stat-card
            :label="__('keywords.customers')"
            :value="$this->totalCustomers"
            iconClass="fas fa-users"
            color="violet"
        />
        <x-stat-card
            :label="__('keywords.suppliers')"
            :value="$this->totalSuppliers"
            iconClass="fas fa-truck"
            color="amber"
        />
    </div>

    {{-- Secondary Stats --}}
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-stat-card
            :label="__('keywords.total_stock')"
            :value="number_format($this->totalStock)"
            iconClass="fas fa-boxes-stacked"
            color="indigo"
        />
        <x-stat-card
            :label="__('keywords.low_stock_items')"
            :value="$this->lowStockCount"
            iconClass="fas fa-triangle-exclamation"
            :color="$this->lowStockCount > 0 ? 'rose' : 'emerald'"
        />
        <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-emerald-600 to-emerald-700 p-5 shadow-sm">
            <p class="text-sm font-medium text-emerald-100">{{ __('keywords.quick_actions') }}</p>
            <div class="mt-3 grid grid-cols-2 gap-2">
                @can('manage_products')
                    <a href="{{ route('products') }}"
                        class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-white backdrop-blur-sm transition-colors hover:bg-white/20">
                        <i class="fas fa-cube text-xs"></i>
                        {{ __('keywords.products') }}
                    </a>
                @endcan
                @can('manage_categories')
                    <a href="{{ route('categories') }}"
                        class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-white backdrop-blur-sm transition-colors hover:bg-white/20">
                        <i class="fas fa-tag text-xs"></i>
                        {{ __('keywords.categories') }}
                    </a>
                @endcan
                @can(['manage_customers', 'view_customers'])
                    <a href="{{ route('customers') }}"
                        class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-white backdrop-blur-sm transition-colors hover:bg-white/20">
                        <i class="fas fa-users text-xs"></i>
                        {{ __('keywords.customers') }}
                    </a>
                @endcan
                @can(['manage_suppliers', 'view_suppliers'])
                    <a href="{{ route('suppliers') }}"
                        class="flex items-center gap-2 rounded-lg bg-white/10 px-3 py-2 text-sm font-medium text-white backdrop-blur-sm transition-colors hover:bg-white/20">
                        <i class="fas fa-truck text-xs"></i>
                        {{ __('keywords.suppliers') }}
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
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

        {{-- Recent Products --}}
        <div class="xl:col-span-1">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clock-rotate-left text-sky-500"></i>
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.recent_products') }}</h3>
                    </div>
                    @can('manage_products')
                        <a href="{{ route('products') }}" class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                            {{ __('keywords.view_all') }}
                        </a>
                    @endcan
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($this->recentProducts as $product)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">{{ $product->category?->name }}</p>
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ $product->quantity }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <p class="text-sm text-gray-500">{{ __('keywords.no_products_found') }}</p>
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
