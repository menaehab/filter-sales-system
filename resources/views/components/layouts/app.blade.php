@props(['title' => 'dashboard'])

@php
    $isRtl = in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']);
    $authUser = auth()->user();
    $unreadNotificationsCount = $authUser?->unreadNotifications()?->count() ?? 0;
    $latestNotifications = $authUser?->notifications()?->latest()->limit(8)->get() ?? collect();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('keywords.' . $title) }} — {{ config('app.name', __('keywords.app')) }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-full">
        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden"
            @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen
                ?
                'translate-x-0' :
                (document.documentElement.dir === 'rtl' ? 'translate-x-full' : '-translate-x-full')"
            class="fixed inset-y-0 start-0 z-50 flex w-64 flex-col bg-gray-900 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:z-auto">
            {{-- Sidebar header --}}
            <div class="flex h-16 items-center gap-3 px-6 border-b border-gray-800">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600">
                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a.75.75 0 0 1 .352-.642l7.5-4.5a.75.75 0 0 1 .796 0l7.5 4.5a.75.75 0 0 1 .352.642" />
                    </svg>
                </div>
                <span class="text-lg font-semibold text-white">{{ __('keywords.app') }}</span>
                <button @click="sidebarOpen = false" class="ms-auto text-gray-400 hover:text-white lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">

                <x-sidebar-link href="{{ route('home') }}" icon="fas fa-house-user" :active="request()->routeIs('home')">
                    {{ __('keywords.home') }}
                </x-sidebar-link>

                @can('manage_categories')
                    <x-sidebar-link href="{{ route('categories') }}" icon="fas fa-tag" :active="request()->routeIs('categories.*')">
                        {{ __('keywords.categories') }}
                    </x-sidebar-link>
                @endcan

                @can('manage_products')
                    <x-sidebar-link href="{{ route('products') }}" icon="fas fa-cube" :active="request()->routeIs('products.*')">
                        {{ __('keywords.products') }}
                    </x-sidebar-link>
                @endcan

                @can(['manage_suppliers', 'view_suppliers'])
                    <x-sidebar-link href="{{ route('suppliers') }}" icon="fas fa-truck" :active="request()->routeIs('suppliers.*')">
                        {{ __('keywords.suppliers') }}
                    </x-sidebar-link>
                @endcan

                @can(['manage_customers', 'view_customers'])
                    <x-sidebar-link href="{{ route('customers') }}" icon="fas fa-users" :active="request()->routeIs('customers.*')">
                        {{ __('keywords.customers') }}
                    </x-sidebar-link>
                @endcan

                @canany(['manage_purchases', 'view_purchases', 'add_purchases', 'edit_purchases', 'pay_purchases'])
                    <x-sidebar-link href="{{ route('purchases') }}" icon="fas fa-file-invoice" :active="request()->routeIs('purchases.*')">
                        {{ __('keywords.purchases') }}
                    </x-sidebar-link>
                @endcanany

                @canany(['manage_purchases_returns', 'view_purchase_returns', 'add_purchase_returns',
                    'edit_purchase_returns'])
                    <x-sidebar-link href="{{ route('purchase-returns') }}" icon="fas fa-rotate-left" :active="request()->routeIs('purchase-returns.*')">
                        {{ __('keywords.purchase_returns') }}
                    </x-sidebar-link>
                @endcanany

                @canany(['manage_sales', 'view_sales', 'add_sales', 'edit_sales', 'pay_sales'])
                    <x-sidebar-link href="{{ route('sales') }}" icon="fas fa-cash-register" :active="request()->routeIs('sales.*')">
                        {{ __('keywords.sales') }}
                    </x-sidebar-link>
                @endcanany

                @can(['manage_supplier_payment_allocations', 'view_supplier_payment_allocations'])
                    <x-sidebar-link href="{{ route('supplier-payments') }}" icon="fas fa-hand-holding-dollar"
                        :active="request()->routeIs('supplier-payments*')">
                        {{ __('keywords.supplier_payments') }}
                    </x-sidebar-link>
                @endcan

                @can(['manage_customer_payment_allocations', 'view_customer_payment_allocations'])
                    <x-sidebar-link href="{{ route('customer-payments') }}" icon="fas fa-sack-dollar" :active="request()->routeIs('customer-payments*')">
                        {{ __('keywords.customer_payments') }}
                    </x-sidebar-link>
                @endcan

                @can('manage_users')
                    <x-sidebar-link href="{{ route('users') }}" icon="fas fa-cog" :active="request()->routeIs('users.*')">
                        {{ __('keywords.users') }}
                    </x-sidebar-link>
                @endcan

            </nav>

            {{-- Sidebar footer --}}
            <div class="border-t border-gray-800 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-700 text-sm font-medium text-white">
                        {{ auth()->user()->name[0] ?? 'A' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name ?? 'Admin User' }}
                        </p>
                        <p class="truncate text-xs text-gray-400">{{ auth()->user()->email ?? 'admin@example.com' }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content area --}}
        <div class="flex flex-1 flex-col min-w-0">
            {{-- Top navbar --}}
            <header
                class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-gray-200 bg-white px-4 sm:px-6">
                {{-- Mobile menu button --}}
                <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                {{-- Page title --}}
                <h1 class="text-lg font-semibold text-gray-900">{{ __('keywords.' . $title) }}</h1>

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Search --}}
                {{-- <div class="hidden sm:block">
                    <div class="relative">
                        <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input type="text" placeholder="Search..." class="w-64 rounded-lg border border-gray-300 bg-gray-50 py-2 ps-10 pe-4 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div> --}}

                {{-- Language / Direction Toggle --}}
                {{-- <x-lang-toggle /> --}}

                {{-- Notifications --}}
                <div class="relative" x-data="{ openNotifications: false }">
                    <button @click="openNotifications = !openNotifications"
                        class="relative rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                        title="{{ __('Notifications') }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>

                        @if ($unreadNotificationsCount > 0)
                            <span
                                class="absolute -top-1 -end-1 min-w-5 rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-bold text-white">
                                {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                            </span>
                        @endif
                    </button>

                    <div x-show="openNotifications" @click.outside="openNotifications = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute end-0 mt-2 w-80 overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black/5">
                        <div class="border-b border-gray-100 px-4 py-3">
                            <p class="text-sm font-semibold text-gray-800">{{ __('keywords.notifications') }}</p>
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            @forelse($latestNotifications as $notification)
                                @php
                                    $notificationMessage = data_get($notification->data, 'message', __('Notification'));
                                    $notificationDate = optional($notification->created_at)->diffForHumans();
                                @endphp
                                <div
                                    class="border-b border-gray-50 px-4 py-3 {{ is_null($notification->read_at) ? 'bg-emerald-50/40' : '' }}">
                                    <p class="text-sm text-gray-700">{{ $notificationMessage }}</p>
                                    <p class="mt-1 text-xs text-gray-400">{{ $notificationDate }}</p>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-center text-sm text-gray-500">
                                    {{ __('keywords.no_notifications') }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- User avatar dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-gray-100">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-sm font-medium text-white">
                            {{ auth()->user()->name[0] ?? 'A' }}
                        </div>
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute end-0 mt-2 w-48 ltr:origin-top-right rtl:origin-top-left rounded-lg bg-white py-1 shadow-lg ring-1 ring-black/5">
                        {{-- <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a> --}}
                        {{-- <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a> --}}
                        {{-- <hr class="my-1 border-gray-100"> --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="block w-full px-4 py-2 text-start text-sm text-gray-700 hover:bg-gray-100">{{ __('keywords.log_out') }}</button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>

</html>
