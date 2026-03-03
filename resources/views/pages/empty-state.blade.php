@props([
    'title' => 'No results found',
    'description' => 'There are no items to display at this time.',
    'actionText' => null,
    'actionHref' => '#',
    'icon' => 'empty',
])

<x-layouts.app :title="$title">
    <div class="flex min-h-[60vh] items-center justify-center">
        <div class="text-center">
            @if($icon === 'empty')
                <svg class="mx-auto h-20 w-20 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="0.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
            @elseif($icon === 'search')
                <svg class="mx-auto h-20 w-20 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="0.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            @elseif($icon === 'error')
                <svg class="mx-auto h-20 w-20 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="0.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            @endif

            <h3 class="mt-6 text-xl font-semibold text-gray-900">{{ $title }}</h3>
            <p class="mt-2 max-w-sm text-sm text-gray-500">{{ $description }}</p>

            @if($actionText)
                <div class="mt-6">
                    <x-button variant="primary" :href="$actionHref">
                        <svg class="-ms-0.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ $actionText }}
                    </x-button>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
