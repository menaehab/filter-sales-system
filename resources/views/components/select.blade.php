@props([
    'label' => null,
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => __('keywords.select_category'),
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'hint' => null,
    'error' => null,
])

@php
    $selectedValue = old($name, $selected);
    if ($multiple && is_string($selectedValue)) {
        $selectedValue = explode(',', $selectedValue);
    }
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $multiple ? $name . '[]' : $name }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $multiple ? 'multiple' : '' }}
        {{ $attributes->except('class')->merge([
            'class' => 'block w-full rounded-lg border text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:bg-gray-100 disabled:cursor-not-allowed py-2.5 ps-3 pe-8'
                . ($errors->has($name) || $error ? ' border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500' : ' border-gray-300 text-gray-900 focus:border-emerald-500 focus:ring-emerald-500')
                . ($multiple ? ' min-h-[120px]' : ''),
        ]) }}
    >
        @unless($multiple)
            <option value="">{{ $placeholder }}</option>
        @endunless

        @foreach($options as $value => $optionLabel)
            <option
                value="{{ $value }}"
                @if($multiple && is_array($selectedValue))
                    {{ in_array($value, $selectedValue) ? 'selected' : '' }}
                @else
                    {{ (string) $selectedValue === (string) $value ? 'selected' : '' }}
                @endif
            >
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @if($hint && !$errors->has($name) && !$error)
        <p class="mt-1.5 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="mt-1.5 text-xs text-red-600">{{ $error }}</p>
    @endif

    @error($name)
        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
