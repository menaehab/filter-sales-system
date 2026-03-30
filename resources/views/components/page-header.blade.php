@props(['title', 'description' => null])

<div
    {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 border-b border-gray-200 pb-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div>
        <h2 class="text-xl font-semibold tracking-tight text-gray-900 sm:text-2xl">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1.5 text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            {{ $actions }}
        </div>
    @endisset
</div>
