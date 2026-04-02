@props([
    'name'     => 'modal',
    'maxWidth' => 'lg',
    'title'    => '',
    'showClose'=> true,
])

@php
    $maxWidthClass = match($maxWidth) {
        'xs'  => 'sm:max-w-xs',
        'sm'  => 'sm:max-w-sm',
        'md'  => 'sm:max-w-md',
        'lg'  => 'sm:max-w-lg',
        'xl'  => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '3xl' => 'sm:max-w-3xl',
        default => 'sm:max-w-lg',
    };
@endphp

<div
    x-data="{ show: false }"
    x-on:open-modal-{{ $name }}.window="show = true; document.body.style.overflow = 'hidden'; $nextTick(() => $el.querySelector('[autofocus], button:not([disabled])')?.focus())"
    x-on:close-modal-{{ $name }}.window="show = false; document.body.style.overflow = ''"
    x-on:keydown.escape.window="if(show){ show = false; document.body.style.overflow = ''; }"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title-{{ $name }}"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-[2px]"
        @click="show = false; document.body.style.overflow = ''"
    ></div>

    {{-- Modal panel --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="show"
            x-transition:enter="ease-out duration-250"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full {{ $maxWidthClass }} overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5"
        >
            {{-- Header --}}
            @if($title || $showClose)
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h3 id="modal-title-{{ $name }}" class="text-base font-semibold text-gray-900">
                        {{ $title }}
                    </h3>
                    @if($showClose)
                        <button
                            @click="show = false; document.body.style.overflow = ''"
                            class="rounded-lg p-1.5 text-gray-400 transition-all duration-150
                                   hover:bg-gray-100 hover:text-gray-600
                                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500"
                            aria-label="{{ __('keywords.close') ?? 'Close' }}"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            {{-- Body --}}
            <div class="px-6 py-5">
                {{ $body ?? $slot }}
            </div>

            {{-- Footer --}}
            @isset($footer)
                <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-gray-100 bg-gray-50/80 px-6 py-4">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
