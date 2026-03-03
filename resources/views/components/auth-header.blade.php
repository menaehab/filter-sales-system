@props([
    'title',
    'description' => null,
])

<div class="text-center">
    <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $title }}</h1>
    @if($description)
        <p class="mt-2 text-sm text-gray-500">{{ $description }}</p>
    @endif
</div>
