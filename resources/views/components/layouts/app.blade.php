@props(['title' => 'dashboard'])

@php
    $isRtl = in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']);
    $authUser = auth()->user();
    $unreadNotificationsCount = $authUser?->unreadNotifications()?->count() ?? 0;
    $latestNotifications = $authUser?->notifications()?->latest()->limit(8)->get() ?? collect();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" class="h-full bg-gray-50">

@include('components.layouts.partials.head')

<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-full">
        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden"
            @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <x-layouts.partials.sidebar />

        {{-- Main content area --}}
        <div class="flex flex-1 flex-col min-w-0">
            <x-layouts.partials.topbar :title="$title" :unreadNotificationsCount="$unreadNotificationsCount" :latestNotifications="$latestNotifications" />

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
    @stack('scripts')
</body>

</html>
