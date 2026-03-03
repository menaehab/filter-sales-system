@props([
    'action' => '',
    'method' => 'POST',
    'hasFiles' => false,
    'submitText' => 'Save',
    'cancelHref' => null,
])

@php
    $spoofedMethod = in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']);
@endphp

<form
    action="{{ $action }}"
    method="{{ $spoofedMethod ? 'POST' : $method }}"
    {{ $hasFiles ? 'enctype=multipart/form-data' : '' }}
    {{ $attributes->merge(['class' => 'space-y-6']) }}
>
    @csrf
    @if($spoofedMethod)
        @method($method)
    @endif

    {{-- Form fields --}}
    <div class="space-y-5">
        {{ $slot }}
    </div>

    {{-- Form actions --}}
    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-5">
        @if($cancelHref)
            <x-button variant="secondary" :href="$cancelHref">Cancel</x-button>
        @endif
        <x-button variant="primary" type="submit">{{ $submitText }}</x-button>
    </div>
</form>
