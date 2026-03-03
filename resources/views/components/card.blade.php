@props([
    'title' => '',
    'description' => '',
])

<div {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @if($title || $description)
        <div>
            @if($title)
                <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            @endif
            @if($description)
                <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
            @endif
        </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        {{ $slot }}
    </div>
</div>
