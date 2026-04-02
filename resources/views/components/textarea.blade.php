@props([
    'label'       => null,
    'name',
    'rows'        => 3,
    'placeholder' => '',
    'value'       => '',
    'required'    => false,
    'disabled'    => false,
    'hint'        => null,
    'error'       => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'w-full space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium leading-6 text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
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
            'class' =>
                'block w-full rounded-xl border bg-white text-sm shadow-sm resize-y ' .
                'transition-all duration-200 placeholder:text-gray-400 ' .
                'focus:outline-none focus:ring-2 focus:ring-offset-0 ' .
                'disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-400 ' .
                'py-2.5 px-3.5 ' .
                ($errors->has($name) || $error
                    ? ' border-red-300 text-red-900 focus:border-red-400 focus:ring-red-400/25'
                    : ' border-gray-300 text-gray-900 hover:border-gray-400 focus:border-emerald-500 focus:ring-emerald-500/25'),
        ]) }}
    >{{ old($name, $value) }}</textarea>

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
