@props([
    'title' => '',
    'description' => '',
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @if ($title || $description)
        <div class="space-y-1">
            @if ($title)
                <h2 class="text-lg font-semibold tracking-tight text-gray-900">{{ $title }}</h2>
            @endif
            @if ($description)
                <p class="text-sm text-gray-500">{{ $description }}</p>
            @endif
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm ring-1 ring-black/2">
        {{ $slot }}
    </div>
</div>
