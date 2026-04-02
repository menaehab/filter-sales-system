@props([
    'icon'        => null,
    'title'       => null,
    'description' => null,
    'colspan'     => 1,
    'action'      => null,
])

<tr>
    <td colspan="{{ $colspan }}" class="px-4 py-16 text-center">
        <div class="flex flex-col items-center">
            {{-- Icon container --}}
            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl
                        bg-gray-100 text-gray-400 ring-1 ring-gray-200/80">
                @if($icon)
                    {{ $icon }}
                @else
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                    </svg>
                @endif
            </div>

            @if($title)
                <p class="text-sm font-semibold text-gray-700">{{ $title }}</p>
            @endif

            @if($description)
                <p class="mt-1 max-w-xs text-sm text-gray-400 leading-relaxed">{{ $description }}</p>
            @endif

            @if($action)
                <div class="mt-5">{{ $action }}</div>
            @endif
        </div>
    </td>
</tr>
