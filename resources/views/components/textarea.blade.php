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

<div {{ $attributes->only('class')->merge(['class' => 'w-full space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium leading-6 text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }} {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->except('class')->merge([
            'class' =>
                'block w-full rounded-xl border bg-white text-sm shadow-sm transition-all duration-200 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500' .
                ($errors->has($name) || $error
                    ? ' border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500/70'
                    : ' border-gray-300 text-gray-900 hover:border-gray-400 focus:border-emerald-500 focus:ring-emerald-500/70') .
                ' py-2.5',
        ]) }}>{{ old($name, $value) }}</textarea>

    @if ($hint && !$errors->has($name) && !$error)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="mt-1 text-xs text-red-600">{{ $error }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
