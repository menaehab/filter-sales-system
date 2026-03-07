@props([
    'searchModel' => 'search',
    'perPageModel' => 'perPage',
    'searchPlaceholder' => null,
    'perPageOptions' => [10, 25, 50, 100],
])

<div {{ $attributes->merge(['class' => 'mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div class="relative w-full sm:max-w-xs">
        <svg class="pointer-events-none absolute inset-s-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input wire:model.live.debounce.300ms="{{ $searchModel }}" type="text"
            placeholder="{{ $searchPlaceholder ?? __('keywords.search') }}"
            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-10 pe-4 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
    </div>

    <div class="flex items-center gap-3">
        {{ $slot }}

        <select wire:model.live="{{ $perPageModel }}"
            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 sm:w-auto">
            @foreach ($perPageOptions as $option)
                <option value="{{ $option }}">{{ __('keywords.per_page', ['count' => $option]) }}</option>
            @endforeach
        </select>
    </div>
</div>
