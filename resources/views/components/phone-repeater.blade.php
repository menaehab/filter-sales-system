@props(['name', 'label' => null, 'placeholder' => null, 'required' => false])

@php
    $phones = data_get($this, $name, []);

    if (!is_array($phones) || $phones === []) {
        $phones = [['number' => '']];
    }
@endphp

<div class="space-y-2">
    @if ($label)
        <div class="block text-sm font-medium leading-6 text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </div>
    @endif

    @foreach ($phones as $index => $phone)
        <div class="flex items-start gap-2">
            <div class="flex-1">
                @php
                    $inputName = "{$name}.{$index}.number";
                    $inputId = str_replace(['.', '[', ']'], '-', $inputName);
                @endphp

                <input id="{{ $inputId }}" name="{{ $inputName }}" type="text"
                    placeholder="{{ $placeholder ?? __('keywords.enter_your_phone') }}"
                    @if ($required && $index === 0) required @endif
                    wire:model.blur="{{ $name }}.{{ $index }}.number" @class([
                        'block w-full rounded-xl border bg-white py-2.5 ps-3.5 pe-3.5 text-sm shadow-sm transition-all duration-200 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-0',
                        'border-red-300 text-red-900 focus:border-red-400 focus:ring-red-400/25' => $errors->has(
                            $inputName),
                        'border-gray-300 text-gray-900 hover:border-gray-400 focus:border-emerald-500 focus:ring-emerald-500/25' => !$errors->has(
                            $inputName),
                    ]) />

                @error($inputName)
                    <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="button" wire:click="removePhoneRow('{{ $name }}', {{ $index }})"
                class="mt-1.5 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-red-200 text-red-600 transition-colors hover:bg-red-50"
                title="{{ __('keywords.delete') }}">
                <i class="fas fa-trash-can text-sm"></i>
            </button>
        </div>
    @endforeach

    <button type="button" wire:click="addPhoneRow('{{ $name }}')"
        class="inline-flex items-center gap-2 text-sm font-medium text-emerald-600 transition-colors hover:text-emerald-700">
        <i class="fas fa-plus text-xs"></i>
        {{ __('keywords.add_phone') }}
    </button>
</div>
