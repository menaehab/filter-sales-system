@props([
    'label',
    'value',
    'icon' => null,
    'trend' => null,
    'trendUp' => true,
    'color' => 'emerald',
])

@php
    $colorClasses = match($color) {
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'sky' => 'bg-sky-50 text-sky-600',
        default => 'bg-emerald-50 text-emerald-600',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-6 shadow-sm']) }}>
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
        @if($icon)
            <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $colorClasses }}">
                {{ $icon }}
            </div>
        @endif
    </div>
    <div class="mt-3">
        <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
        @if($trend)
            <div class="mt-1 flex items-center gap-1 text-sm {{ $trendUp ? 'text-emerald-600' : 'text-red-600' }}">
                @if($trendUp)
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                    </svg>
                @else
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.986l.776 5.15m0 0 2.96-4.725m-2.96 4.725-4.724-2.956" />
                    </svg>
                @endif
                <span class="font-medium">{{ $trend }}</span>
                <span class="text-gray-400">vs last month</span>
            </div>
        @endif
    </div>
</div>
