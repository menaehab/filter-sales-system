@props([
    'label' => null,
    'name',
    'options' => [],
    'selected' => [],
    'disabled' => false,
    'error' => null,
])

@php
    $selectedValues = old($name, is_array($selected) ? $selected : [$selected]);
@endphp

<fieldset {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if($label)
        <legend class="mb-2 text-sm font-medium text-gray-700">{{ $label }}</legend>
    @endif

    <div class="grid grid-cols-2 gap-x-4 gap-y-2">
        @foreach($options as $value => $optionLabel)
            <div class="relative flex items-start">
                <div class="flex h-6 items-center">
                    <input
                        id="{{ $name }}-{{ $value }}"
                        name="{{ $name }}[]"
                        type="checkbox"
                        value="{{ $value }}"
                        wire:model="{{ $name }}"
                        {{ in_array($value, $selectedValues) ? 'checked' : '' }}
                        {{ $disabled ? 'disabled' : '' }}
                        class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                </div>
                <div class="ms-3 text-sm leading-6">
                    <label for="{{ $name }}-{{ $value }}" class="font-medium text-gray-700 {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                        {{ $optionLabel }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>

    @if($error)
        <p class="mt-1.5 text-xs text-red-600">{{ $error }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</fieldset>
