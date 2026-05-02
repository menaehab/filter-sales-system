<aside
    :class="sidebarOpen
        ?
        'translate-x-0' :
        (document.documentElement.dir === 'rtl' ? 'translate-x-full' : '-translate-x-full')"
    class="fixed inset-y-0 inset-s-0 z-50 flex w-64 flex-col bg-gray-900 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:z-auto">

    {{-- Sidebar header --}}
    <div class="flex h-16 items-center gap-3 px-6">
        <div class="flex h-12 w-14 items-center justify-center rounded-lg overflow-hidden shadow-sm">
            <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="h-full w-full object-cover rounded-lg">
        </div>
        <span class="text-md font-semibold text-white">{{ __('keywords.app') }}</span>
        <button @click="sidebarOpen = false"
            class="ms-auto text-gray-400 hover:text-white lg:hidden transition-colors duration-200">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 space-y-2 px-3 py-4 overflow-y-auto" x-data="{
        init() {
                const saved = localStorage.getItem('sidebarOpenGroups');
                if (saved) {
                    try {
                        const parsed = JSON.parse(saved);
                        this.openGroups = { ...this.openGroups, ...parsed };
                    } catch (e) {}
                }
            },
            save() {
                localStorage.setItem('sidebarOpenGroups', JSON.stringify(this.openGroups));
            },
            openGroups: {
                main: {{ request()->routeIs('home') ? 'true' : 'false' }},
                sales: {{ request()->routeIs('sales*') || request()->routeIs('overdue-installments*') ? 'true' : 'false' }},
                purchases: {{ request()->routeIs('purchases*') ? 'true' : 'false' }},
                people: {{ request()->routeIs('customers*') || request()->routeIs('suppliers*') || request()->routeIs('filters*') || request()->routeIs('service-visits*') ? 'true' : 'false' }},
                inventory: {{ request()->routeIs('categories*') || request()->routeIs('products*') || request()->routeIs('damaged-products*') || request()->routeIs('expenses*') ? 'true' : 'false' }},
                system: {{ request()->routeIs('dashboard') || request()->routeIs('activities*') || request()->routeIs('users*') || request()->routeIs('places*') ? 'true' : 'false' }},
            },
            toggleGroup(key) {
                this.openGroups[key] = !this.openGroups[key];
                this.save();
            }
    }">

        {{-- Main --}}
        <div class="space-y-1">
            <button type="button" @click="toggleGroup('main')"
                class="w-full flex items-center justify-between rounded-lg px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400 hover:text-gray-200 transition-colors">
                <span>{{ __('keywords.sidebar_group_main') }}</span>
                <i class="fas fa-chevron-down text-[10px] transition-transform"
                    :class="openGroups.main ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="openGroups.main" x-collapse class="space-y-1">
                <x-sidebar-link href="{{ route('home') }}" icon="fas fa-house-user" :active="request()->routeIs('home')">
                    {{ __('keywords.home') }}
                </x-sidebar-link>
            </div>
        </div>

        {{-- Sales --}}
        @canany(['manage_sales', 'view_sales', 'add_sales', 'edit_sales', 'pay_sales',
            'view_sale_returns', 'add_sale_returns', 'edit_sale_returns', 'manage_sale_returns',
            'manage_customer_payment_allocations', 'view_customer_payment_allocations',
            'view_overdue_installments'])
            <div class="space-y-1">
                <button type="button" @click="toggleGroup('sales')"
                    class="w-full flex items-center justify-between rounded-lg px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400 hover:text-gray-200 transition-colors">
                    <span>{{ __('keywords.sidebar_group_sales') }}</span>
                    <i class="fas fa-chevron-down text-[10px] transition-transform"
                        :class="openGroups.sales ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openGroups.sales" x-collapse class="space-y-1">
                    @canany(['manage_sales', 'view_sales', 'add_sales', 'edit_sales', 'pay_sales'])
                        <x-sidebar-link href="{{ route('sales') }}" icon="fas fa-cash-register" :active="request()->routeIs('sales') || request()->routeIs('sales.show*') || request()->routeIs('sales.create*') || request()->routeIs('sales.edit*')">
                            {{ __('keywords.sales') }}
                        </x-sidebar-link>
                    @endcanany
                    @can('view_overdue_installments')
                        <x-sidebar-link href="{{ route('overdue-installments') }}" icon="fas fa-clock" :active="request()->routeIs('overdue-installments*')">
                            {{ __('keywords.overdue_installments') }}
                        </x-sidebar-link>
                    @endcan
                    @canany(['manage_sale_returns', 'view_sale_returns', 'add_sale_returns', 'edit_sale_returns'])
                        <x-sidebar-link href="{{ route('sale-returns') }}" icon="fas fa-rotate-left" :active="request()->routeIs('sale-returns*')">
                            {{ __('keywords.sale_returns') }}
                        </x-sidebar-link>
                    @endcanany
                    @canany(['manage_customer_payment_allocations', 'view_customer_payment_allocations'])
                        <x-sidebar-link href="{{ route('customer-payments') }}" icon="fas fa-sack-dollar" :active="request()->routeIs('customer-payments*')">
                            {{ __('keywords.customer_payments') }}
                        </x-sidebar-link>
                    @endcanany
                </div>
            </div>
        @endcanany

        {{-- Purchases --}}
        @canany(['manage_purchases', 'view_purchases', 'add_purchases', 'edit_purchases', 'pay_purchases',
            'manage_purchase_returns', 'view_purchase_returns', 'add_purchase_returns', 'edit_purchase_returns',
            'manage_supplier_payment_allocations', 'view_supplier_payment_allocations'])
            <div class="space-y-1">
                <button type="button" @click="toggleGroup('purchases')"
                    class="w-full flex items-center justify-between rounded-lg px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400 hover:text-gray-200 transition-colors">
                    <span>{{ __('keywords.sidebar_group_purchases') }}</span>
                    <i class="fas fa-chevron-down text-[10px] transition-transform"
                        :class="openGroups.purchases ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openGroups.purchases" x-collapse class="space-y-1">
                    @canany(['manage_purchases', 'view_purchases', 'add_purchases', 'edit_purchases', 'pay_purchases'])
                        <x-sidebar-link href="{{ route('purchases') }}" icon="fas fa-file-invoice" :active="request()->routeIs('purchases*')">
                            {{ __('keywords.purchases') }}
                        </x-sidebar-link>
                    @endcanany
                    @canany(['manage_purchase_returns', 'view_purchase_returns', 'add_purchase_returns',
                        'edit_purchase_returns'])
                        <x-sidebar-link href="{{ route('purchase-returns') }}" icon="fas fa-rotate-left" :active="request()->routeIs('purchase-returns*')">
                            {{ __('keywords.purchase_returns') }}
                        </x-sidebar-link>
                    @endcanany
                    @canany(['manage_supplier_payment_allocations', 'view_supplier_payment_allocations'])
                        <x-sidebar-link href="{{ route('supplier-payments') }}" icon="fas fa-hand-holding-dollar"
                            :active="request()->routeIs('supplier-payments*')">
                            {{ __('keywords.supplier_payments') }}
                        </x-sidebar-link>
                    @endcanany
                </div>
            </div>
        @endcanany

        {{-- People --}}
        @canany(['manage_customers', 'view_customers', 'manage_suppliers', 'view_suppliers', 'manage_water_filters',
            'view_water_filters', 'manage_service_visits', 'view_service_visits'])
            <div class="space-y-1">
                <button type="button" @click="toggleGroup('people')"
                    class="w-full flex items-center justify-between rounded-lg px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400 hover:text-gray-200 transition-colors">
                    <span>{{ __('keywords.sidebar_group_people') }}</span>
                    <i class="fas fa-chevron-down text-[10px] transition-transform"
                        :class="openGroups.people ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openGroups.people" x-collapse class="space-y-1">
                    @canany(['manage_customers', 'view_customers'])
                        <x-sidebar-link href="{{ route('customers') }}" icon="fas fa-users" :active="request()->routeIs('customers*')">
                            {{ __('keywords.customers') }}
                        </x-sidebar-link>
                    @endcanany
                    @canany(['manage_suppliers', 'view_suppliers'])
                        <x-sidebar-link href="{{ route('suppliers') }}" icon="fas fa-truck" :active="request()->routeIs('suppliers*')">
                            {{ __('keywords.suppliers') }}
                        </x-sidebar-link>
                    @endcanany
                    @canany(['manage_water_filters', 'view_water_filters'])
                        <x-sidebar-link href="{{ route('filters') }}" icon="fas fa-filter" :active="request()->routeIs('filters*')">
                            {{ __('keywords.filters') }}
                        </x-sidebar-link>
                    @endcanany
                    @canany(['manage_service_visits', 'view_service_visits'])
                        <x-sidebar-link href="{{ route('service-visits') }}" icon="fas fa-screwdriver-wrench"
                            :active="request()->routeIs('service-visits*')">
                            {{ __('keywords.service_visits') }}
                        </x-sidebar-link>
                    @endcanany
                </div>
            </div>
        @endcanany

        {{-- Inventory --}}
        @canany(['manage_categories', 'manage_products', 'manage_damaged_products', 'view_damaged_products',
            'manage_expenses', 'view_expenses'])
            <div class="space-y-1">
                <button type="button" @click="toggleGroup('inventory')"
                    class="w-full flex items-center justify-between rounded-lg px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400 hover:text-gray-200 transition-colors">
                    <span>{{ __('keywords.sidebar_group_inventory') }}</span>
                    <i class="fas fa-chevron-down text-[10px] transition-transform"
                        :class="openGroups.inventory ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openGroups.inventory" x-collapse class="space-y-1">
                    @can('manage_categories')
                        <x-sidebar-link href="{{ route('categories') }}" icon="fas fa-tag" :active="request()->routeIs('categories*')">
                            {{ __('keywords.categories') }}
                        </x-sidebar-link>
                    @endcan
                    @can('manage_products')
                        <x-sidebar-link href="{{ route('products') }}" icon="fas fa-cube" :active="request()->routeIs('products*')">
                            {{ __('keywords.products') }}
                        </x-sidebar-link>
                    @endcan
                    @canany(['manage_damaged_products', 'view_damaged_products'])
                        <x-sidebar-link href="{{ route('damaged-products') }}" icon="fas fa-trash-alt" :active="request()->routeIs('damaged-products*')">
                            {{ __('keywords.damaged_products') }}
                        </x-sidebar-link>
                    @endcanany
                    @canany(['manage_expenses', 'view_expenses'])
                        <x-sidebar-link href="{{ route('expenses') }}" icon="fas fa-wallet" :active="request()->routeIs('expenses*')">
                            {{ __('keywords.expenses') }}
                        </x-sidebar-link>
                    @endcanany
                </div>
            </div>
        @endcanany

        {{-- System --}}
        @canany(['view_dashboard', 'view_activities', 'manage_users', 'manage_places', 'view_places'])
            <div class="space-y-1">
                <button type="button" @click="toggleGroup('system')"
                    class="w-full flex items-center justify-between rounded-lg px-2 py-1.5 text-xs font-semibold uppercase tracking-wider text-gray-400 hover:text-gray-200 transition-colors">
                    <span>{{ __('keywords.sidebar_group_system') }}</span>
                    <i class="fas fa-chevron-down text-[10px] transition-transform"
                        :class="openGroups.system ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="openGroups.system" x-collapse class="space-y-1">
                    @can('view_dashboard')
                        <x-sidebar-link href="{{ route('dashboard') }}" icon="fas fa-bar-chart" :active="request()->routeIs('dashboard')">
                            {{ __('keywords.dashboard') }}
                        </x-sidebar-link>
                    @endcan
                    @can('view_activities')
                        <x-sidebar-link href="{{ route('activities') }}" icon="fas fa-list" :active="request()->routeIs('activities*')">
                            {{ __('keywords.activity_logs') }}
                        </x-sidebar-link>
                    @endcan
                    @can('manage_users')
                        <x-sidebar-link href="{{ route('users') }}" icon="fas fa-cog" :active="request()->routeIs('users*')">
                            {{ __('keywords.users') }}
                        </x-sidebar-link>
                    @endcan
                    @canany(['manage_places', 'view_places'])
                        <x-sidebar-link href="{{ route('places') }}" icon="fas fa-location-dot" :active="request()->routeIs('places*')">
                            {{ __('keywords.places') }}
                        </x-sidebar-link>
                    @endcanany
                </div>
            </div>
        @endcanany

    </nav>

    {{-- Sidebar footer --}}
    <div class="border-t border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div
                class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-700 text-sm font-medium text-white">
                {{ auth()->user()->name[0] ?? 'A' }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name ?? 'Admin User' }}</p>
                <p class="truncate text-xs text-gray-400">{{ auth()->user()->email ?? 'admin@example.com' }}</p>
            </div>
        </div>
    </div>
</aside>
