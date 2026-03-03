@props([
    'label' => null,
    'name',
    'value' => '1',
    'checked' => false,
    'disabled' => false,
    'description' => null,
    'error' => null,
])

@php
    $isChecked = old($name, $checked);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'relative flex items-start']) }}>
    <div class="flex h-6 items-center">
        <input
            id="{{ $name }}-{{ $value }}"
            name="{{ $name }}"
            type="checkbox"
            value="{{ $value }}"
            {{ $isChecked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->except('class')->merge([
                'class' => 'h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed',
            ]) }}
        >
    </div>
    @if($label || $description)
        <div class="ms-3 text-sm leading-6">
            @if($label)
                <label for="{{ $name }}-{{ $value }}" class="font-medium text-gray-900 {{ $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                    {{ $label }}
                </label>
            @endif
            @if($description)
                <p class="text-gray-500">{{ $description }}</p>
            @endif
        </div>
    @endif

    @if($error)
        <p class="mt-1.5 text-xs text-red-600">{{ $error }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
