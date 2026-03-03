@props([
    'label',
    'color' => 'gray',
])

@php
    $colorClasses = match($color) {
        'gray' => 'bg-gray-100 text-gray-700',
        'green' => 'bg-emerald-100 text-emerald-700',
        'red' => 'bg-red-100 text-red-700',
        'yellow' => 'bg-amber-100 text-amber-700',
        'blue' => 'bg-blue-100 text-blue-700',
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'purple' => 'bg-purple-100 text-purple-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium $colorClasses"]) }}>
    {{ $label }}
</span>
