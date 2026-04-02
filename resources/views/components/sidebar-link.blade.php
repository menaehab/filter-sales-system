@props([
    'href'   => '#',
    'icon'   => 'fa-solid fa-house',
    'active' => false,
])

<a href="{{ $href }}" wire:navigate
    {{ $attributes->class([
        'group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium ' .
        'transition-all duration-200 outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/70',
        'bg-gray-800 text-white shadow-sm'               => $active,
        'text-gray-300 hover:bg-gray-800/70 hover:text-white' => !$active,
    ]) }}
    @if($active) aria-current="page" @endif
>
    {{-- Animated active indicator bar --}}
    <span class="absolute inset-y-2 start-0 w-0.5 rounded-e-full bg-emerald-500 transition-all duration-300
                 {{ $active ? 'opacity-100 scale-y-100' : 'opacity-0 scale-y-0' }}"
          aria-hidden="true">
    </span>

    {{-- Icon --}}
    <i class="{{ $icon }} w-5 shrink-0 text-center text-[15px] transition-colors duration-200
              {{ $active ? 'text-emerald-400' : 'text-gray-400 group-hover:text-gray-200' }}"
       aria-hidden="true">
    </i>

    {{-- Label --}}
    <span class="truncate">{{ $slot }}</span>
</a>
