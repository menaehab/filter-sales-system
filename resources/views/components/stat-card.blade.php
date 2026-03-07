@props([
    'label',
    'value',
    'icon' => null,
    'iconClass' => null,
    'trend' => null,
    'trendUp' => true,
    'color' => 'emerald',
])

@php
    $colorClasses = match($color) {
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'violet' => 'bg-violet-50 text-violet-600',
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'orange' => 'bg-orange-50 text-orange-600',
        default => 'bg-emerald-50 text-emerald-600',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow']) }}>
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
        @if($iconClass)
            <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $colorClasses }}">
                <i class="{{ $iconClass }} text-base"></i>
            </div>
        @elseif($icon)
            <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $colorClasses }}">
                {{ $icon }}
            </div>
        @endif
    </div>
    <div class="mt-3">
        <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
        @if($trend)
            <div class="mt-1 flex items-center gap-1 text-sm {{ $trendUp ? 'text-emerald-600' : 'text-red-600' }}">
                <i class="fas {{ $trendUp ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-xs"></i>
                <span class="font-medium">{{ $trend }}</span>
            </div>
        @endif
    </div>
</div>
