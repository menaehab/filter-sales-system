@props([
    'label' => null,
    'name',
    'rows' => 3,
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'error' => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if($label)
        <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->except('class')->merge([
            'class' => 'block w-full rounded-lg border text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:bg-gray-100 disabled:cursor-not-allowed'
                . ($errors->has($name) || $error ? ' border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500' : ' border-gray-300 text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500')
                . ' py-2.5',
        ]) }}>{{ old($name, $value) }}</textarea>

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
