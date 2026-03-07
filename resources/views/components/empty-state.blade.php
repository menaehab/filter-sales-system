@props([
    'icon' => null,
    'title' => null,
    'description' => null,
    'colspan' => 1,
])

<tr>
    <td colspan="{{ $colspan }}" class="px-4 py-12 text-center">
        <div class="flex flex-col items-center">
            @if($icon)
                <div class="mb-3 text-gray-300">
                    {{ $icon }}
                </div>
            @else
                <svg class="mb-3 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                </svg>
            @endif
            @if($title)
                <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
            @endif
            @if($description)
                <p class="mt-1 text-xs text-gray-400">{{ $description }}</p>
            @endif
        </div>
    </td>
</tr>
