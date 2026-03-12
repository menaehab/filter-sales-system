@props([
    'name' => 'confirm',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'confirmText' => 'Confirm',
    'cancelText' => __('keywords.cancel'),
    'variant' => 'danger',
])

<x-modal :name="$name" :title="$title" maxWidth="sm">
    <x-slot:body>
        <p class="text-sm text-gray-600">{{ $message }}</p>
    </x-slot:body>

    <x-slot:footer>
        <x-button variant="secondary" @click="$dispatch('close-modal-{{ $name }}')">
            {{ $cancelText }}
        </x-button>
        <x-button :variant="$variant" @click="$dispatch('confirmed-{{ $name }}'); $dispatch('close-modal-{{ $name }}')">
            {{ $confirmText }}
        </x-button>
    </x-slot:footer>
</x-modal>
