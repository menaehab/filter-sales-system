@props([
    'name'        => 'confirm',
    'title'       => 'Confirm Action',
    'message'     => 'Are you sure you want to proceed?',
    'confirmText' => 'Confirm',
    'cancelText'  => __('keywords.cancel'),
    'variant'     => 'danger',
])

<x-modal :name="$name" :title="$title" maxWidth="sm">
    <x-slot:body>
        <div class="flex items-start gap-4">
            @if($variant === 'danger')
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 ring-1 ring-red-100">
                    <i class="fas fa-triangle-exclamation text-sm text-red-500" aria-hidden="true"></i>
                </div>
            @elseif($variant === 'warning')
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-50 ring-1 ring-amber-100">
                    <i class="fas fa-exclamation text-sm text-amber-500" aria-hidden="true"></i>
                </div>
            @endif
            <p class="pt-2 text-sm leading-relaxed text-gray-600">{{ $message }}</p>
        </div>
    </x-slot:body>

    <x-slot:footer>
        <x-button variant="secondary" @click="$dispatch('close-modal-{{ $name }}')">
            {{ $cancelText }}
        </x-button>
        <x-button
            :variant="$variant"
            autofocus
            @click="$dispatch('confirmed-{{ $name }}'); $dispatch('close-modal-{{ $name }}')"
        >
            {{ $confirmText }}
        </x-button>
    </x-slot:footer>
</x-modal>
