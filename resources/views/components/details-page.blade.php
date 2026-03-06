@props([
    'title',
    'subtitle' => null,
    'backUrl' => null,
    'backLabel' => null,
    'badge' => null,
    'badgeColor' => 'emerald',
])

<div {{ $attributes->merge(['class' => 'space-y-6']) }}>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-2">
            @if ($backUrl)
                <a href="{{ $backUrl }}"
                    class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition-colors hover:text-emerald-600">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    <span>{{ $backLabel ?? __('keywords.back') }}</span>
                </a>
            @endif

            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">{{ $title }}</h2>
                @if ($badge)
                    <x-badge :label="$badge" :color="$badgeColor" />
                @endif
            </div>

            @if ($subtitle)
                <p class="text-sm text-gray-500">{{ $subtitle }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @isset($stats)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{ $stats }}
        </div>
    @endisset

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                {{ $slot }}
            </div>
        </div>

        @isset($aside)
            <div class="space-y-6">
                {{ $aside }}
            </div>
        @endisset
    </div>
</div>
