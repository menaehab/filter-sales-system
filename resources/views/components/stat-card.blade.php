@props([
    'label',
    'value',
    'icon'      => null,
    'iconClass' => null,
    'trend'     => null,
    'trendUp'   => true,
    'color'     => 'emerald',
    'suffix'    => null,
    'loading'   => false,
])

@php
    $palette = [
        'emerald' => ['icon' => 'bg-emerald-50 text-emerald-600', 'ring' => 'hover:ring-emerald-200/80'],
        'blue'    => ['icon' => 'bg-blue-50 text-blue-600',       'ring' => 'hover:ring-blue-200/80'],
        'amber'   => ['icon' => 'bg-amber-50 text-amber-600',     'ring' => 'hover:ring-amber-200/80'],
        'rose'    => ['icon' => 'bg-rose-50 text-rose-600',       'ring' => 'hover:ring-rose-200/80'],
        'teal'    => ['icon' => 'bg-teal-50 text-teal-600',       'ring' => 'hover:ring-teal-200/80'],
        'violet'  => ['icon' => 'bg-violet-50 text-violet-600',   'ring' => 'hover:ring-violet-200/80'],
        'indigo'  => ['icon' => 'bg-indigo-50 text-indigo-600',   'ring' => 'hover:ring-indigo-200/80'],
        'orange'  => ['icon' => 'bg-orange-50 text-orange-600',   'ring' => 'hover:ring-orange-200/80'],
        'sky'     => ['icon' => 'bg-sky-50 text-sky-600',         'ring' => 'hover:ring-sky-200/80'],
        'red'     => ['icon' => 'bg-red-50 text-red-600',         'ring' => 'hover:ring-red-200/80'],
        'purple'  => ['icon' => 'bg-purple-50 text-purple-600',   'ring' => 'hover:ring-purple-200/80'],
    ];
    $p = $palette[$color] ?? $palette['emerald'];
@endphp

<div {{ $attributes->merge(['class' =>
    'group relative rounded-2xl border border-gray-200 bg-white p-5 shadow-sm ring-1 ring-transparent ' .
    'transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md ' . $p['ring']
]) }}>
    @if($loading)
        {{-- Skeleton loader --}}
        <div class="flex items-center justify-between">
            <div class="h-3.5 w-24 rounded-lg skeleton bg-gray-200"></div>
            <div class="h-10 w-10 rounded-xl skeleton bg-gray-200"></div>
        </div>
        <div class="mt-4 h-7 w-32 rounded-lg skeleton bg-gray-200"></div>
        <div class="mt-2 h-3 w-20 rounded skeleton bg-gray-100"></div>
    @else
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            @if ($iconClass || $icon)
                <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $p['icon'] }}
                            transition-transform duration-200 group-hover:scale-110">
                    @if($iconClass)
                        <i class="{{ $iconClass }} text-base" aria-hidden="true"></i>
                    @else
                        {{ $icon }}
                    @endif
                </div>
            @endif
        </div>

        <div class="mt-3.5">
            <p class="text-2xl font-bold tracking-tight text-gray-900">
                {{ $value }}
                @if($suffix)
                    <span class="text-sm font-medium text-gray-400 ms-1">{{ $suffix }}</span>
                @endif
            </p>
            @if ($trend)
                <div class="mt-1.5 flex items-center gap-1.5 text-sm
                            {{ $trendUp ? 'text-emerald-600' : 'text-red-500' }}">
                    <i class="fas {{ $trendUp ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-xs" aria-hidden="true"></i>
                    <span class="font-medium">{{ $trend }}</span>
                </div>
            @endif
        </div>
    @endif
</div>
