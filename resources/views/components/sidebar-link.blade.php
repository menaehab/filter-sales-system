@props([
    'href' => '#',
    'icon' => 'fa-solid fa-house',
    'active' => false,
])

<a href="{{ $href }}"
    {{ $attributes->class([
        'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
        'bg-gray-800 text-white' => $active,
        'text-gray-300 hover:bg-gray-800 hover:text-white' => !$active,
    ]) }}>

    <i class="{{ $icon }} w-5 text-center
        {{ $active ? 'text-white' : 'text-gray-400 group-hover:text-white' }}">
    </i>

    {{ $slot }}
</a>
