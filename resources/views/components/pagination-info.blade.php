@props([
    'paginator',
])

<div {{ $attributes->merge(['class' => 'mt-4 flex flex-col items-center justify-between gap-4 sm:flex-row']) }}>
    <p class="text-sm text-gray-500">
        {{ __('keywords.showing') }}
        <span class="font-medium text-gray-700">{{ $paginator->firstItem() ?? 0 }}</span>
        {{ __('keywords.to') }}
        <span class="font-medium text-gray-700">{{ $paginator->lastItem() ?? 0 }}</span>
        {{ __('keywords.of') }}
        <span class="font-medium text-gray-700">{{ $paginator->total() }}</span>
        {{ __('keywords.results') }}
    </p>
    <div>
        {{ $paginator->links() }}
    </div>
</div>
