@props([
    'label'       => null,
    'name',
    'options'     => [],
    'selected'    => null,
    'placeholder' => __('keywords.select_category'),
    'required'    => false,
    'disabled'    => false,
    'multiple'    => false,
    'hint'        => null,
    'error'       => null,
])

@php
    $selectedValue = old($name, $selected);
    if ($multiple && is_string($selectedValue)) {
        $selectedValue = explode(',', $selectedValue);
    }

    $hasCustomOptions = isset($slot) && trim((string) $slot) !== '';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium leading-6 text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
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
            'class' =>
                'block w-full rounded-xl border bg-white py-2.5 ps-3.5 pe-9 text-sm shadow-sm ' .
                'transition-all duration-200 ' .
                'focus:outline-none focus:ring-2 focus:ring-offset-0 ' .
                'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 ' .
                ($errors->has($name) || $error
                    ? ' border-red-300 text-red-900 focus:border-red-400 focus:ring-red-400/25'
                    : ' border-gray-300 text-gray-900 hover:border-gray-400 focus:border-emerald-500 focus:ring-emerald-500/25') .
                ($multiple ? ' min-h-[140px]' : ''),
        ]) }}
    >
        @unless ($multiple || $hasCustomOptions)
            <option value="">{{ $placeholder }}</option>
        @endunless

        @if ($hasCustomOptions)
            {{ $slot }}
        @else
            @foreach ($options as $value => $optionLabel)
                <option
                    value="{{ $value }}"
                    @if ($multiple && is_array($selectedValue)) {{ in_array($value, $selectedValue) ? 'selected' : '' }}
                    @else {{ (string) $selectedValue === (string) $value ? 'selected' : '' }}
                    @endif
                >
                    {{ $optionLabel }}
                </option>
            @endforeach
        @endif
    </select>

    @if ($hint && !$errors->has($name) && !$error)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="mt-1 text-xs text-red-600" role="alert">{{ $error }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
    @enderror
</div>
