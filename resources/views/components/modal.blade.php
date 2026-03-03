@props([
    'name' => 'modal',
    'maxWidth' => 'lg',
    'title' => '',
    'showClose' => true,
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        default => 'sm:max-w-lg',
    };
@endphp

<div
    x-data="{ show: false }"
    x-on:open-modal-{{ $name }}.window="show = true"
    x-on:close-modal-{{ $name }}.window="show = false"
    x-on:keydown.escape.window="show = false"
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
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"
        @click="show = false"
    ></div>

    {{-- Modal panel --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative w-full {{ $maxWidthClass }} overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black/5"
            @click.outside="show = false"
        >
            {{-- Header --}}
            @if($title || $showClose)
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <h3 id="modal-title-{{ $name }}" class="text-lg font-semibold text-gray-900">
                        {{ $title }}
                    </h3>
                    @if($showClose)
                        <button @click="show = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            {{-- Body --}}
            <div class="px-6 py-4">
                {{ $body ?? $slot }}
            </div>

            {{-- Footer --}}
            @isset($footer)
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
