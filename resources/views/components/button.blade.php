@props([
    'variant'  => 'primary',
    'size'     => 'md',
    'type'     => 'button',
    'disabled' => false,
    'href'     => null,
    'loading'  => false,
    'icon'     => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl font-medium ' .
            'transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 ' .
            'active:scale-[0.97] disabled:cursor-not-allowed disabled:opacity-50 select-none whitespace-nowrap';

    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs',
        'sm' => 'px-3.5 py-2 text-xs',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
        'xl' => 'px-8 py-4 text-lg',
    ];

    $variants = [
        'primary'   => 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 ' .
                       'hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/30 ' .
                       'focus-visible:ring-emerald-500',
        'secondary' => 'border border-gray-300 bg-white text-gray-700 shadow-sm ' .
                       'hover:bg-gray-50 hover:border-gray-400 hover:text-gray-900 ' .
                       'focus-visible:ring-emerald-500',
        'danger'    => 'bg-red-600 text-white shadow-sm shadow-red-600/20 ' .
                       'hover:bg-red-700 hover:shadow-md hover:shadow-red-600/30 ' .
                       'focus-visible:ring-red-500',
        'warning'   => 'bg-amber-500 text-white shadow-sm shadow-amber-500/20 ' .
                       'hover:bg-amber-600 hover:shadow-md focus-visible:ring-amber-500',
        'success'   => 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 ' .
                       'hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/30 ' .
                       'focus-visible:ring-emerald-500',
        'ghost'     => 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus-visible:ring-emerald-500',
        'link'      => 'text-emerald-600 underline-offset-4 hover:underline focus-visible:ring-emerald-500 px-0 py-0 shadow-none',
        'info'      => 'bg-sky-600 text-white shadow-sm shadow-sky-600/20 ' .
                       'hover:bg-sky-700 hover:shadow-md focus-visible:ring-sky-500',
    ];

    $classes = $base . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
    $isDisabled = $disabled || $loading;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($loading)
            <svg class="h-4 w-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        @elseif($icon)
            <i class="{{ $icon }} shrink-0" aria-hidden="true"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $isDisabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if($loading)
            <svg class="h-4 w-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        @elseif($icon)
            <i class="{{ $icon }} shrink-0" aria-hidden="true"></i>
        @endif
        {{ $slot }}
    </button>
@endif
