@props([
    'label',
    'color' => 'gray',
    'dot'   => false,
    'pill'  => true,
])

@php
    $colors = [
        'gray'    => 'bg-gray-100 text-gray-700 ring-gray-200',
        'green'   => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'emerald' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'red'     => 'bg-red-100 text-red-700 ring-red-200',
        'yellow'  => 'bg-amber-100 text-amber-700 ring-amber-200',
        'amber'   => 'bg-amber-100 text-amber-700 ring-amber-200',
        'blue'    => 'bg-blue-100 text-blue-700 ring-blue-200',
        'purple'  => 'bg-purple-100 text-purple-700 ring-purple-200',
        'rose'    => 'bg-rose-100 text-rose-700 ring-rose-200',
        'orange'  => 'bg-orange-100 text-orange-700 ring-orange-200',
        'sky'     => 'bg-sky-100 text-sky-700 ring-sky-200',
        'teal'    => 'bg-teal-100 text-teal-700 ring-teal-200',
        'violet'  => 'bg-violet-100 text-violet-700 ring-violet-200',
        'indigo'  => 'bg-indigo-100 text-indigo-700 ring-indigo-200',
    ];
    $dots = [
        'gray'    => 'bg-gray-500',
        'green'   => 'bg-emerald-500',
        'emerald' => 'bg-emerald-500',
        'red'     => 'bg-red-500',
        'yellow'  => 'bg-amber-500',
        'amber'   => 'bg-amber-500',
        'blue'    => 'bg-blue-500',
        'purple'  => 'bg-purple-500',
        'rose'    => 'bg-rose-500',
        'orange'  => 'bg-orange-500',
        'sky'     => 'bg-sky-500',
        'teal'    => 'bg-teal-500',
        'violet'  => 'bg-violet-500',
        'indigo'  => 'bg-indigo-500',
    ];
    $c = $colors[$color] ?? $colors['gray'];
    $d = $dots[$color] ?? $dots['gray'];
@endphp

<span {{ $attributes->merge(['class' =>
    'inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ' . $c .
    ($pill ? ' rounded-full' : ' rounded-md')
]) }}>
    @if($dot)
        <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $d }}" aria-hidden="true"></span>
    @endif
    {{ $label }}
</span>
