@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' =>
    'mb-6 pb-5 border-b border-gray-100 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between'
]) }}>
    <div>
        <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1.5 text-sm leading-relaxed text-gray-500">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-shrink-0 flex-wrap items-center gap-2 sm:justify-end">
            {{ $actions }}
        </div>
    @endisset
</div>
