@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
    'href' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2.5 text-sm',
    };

    $variantClasses = match($variant) {
        'primary' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-emerald-500',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'ghost' => 'text-gray-700 hover:bg-gray-100 focus:ring-emerald-500',
        default => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
    };

    $classes = "$baseClasses $sizeClasses $variantClasses";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
