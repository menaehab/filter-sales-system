@props([
    'editAction' => null,
    'deleteAction' => null,
    'viewUrl' => null,
    'canView' => true,
    'canEdit' => true,
    'canDelete' => true,
])

<div class="flex items-center justify-end gap-1">
    @if ($viewUrl && $canView)
        <a href="{{ $viewUrl }}" title="{{ __('keywords.view') ?? 'View' }}"
            aria-label="{{ __('keywords.view') ?? 'View' }}"
            class="inline-flex h-8 items-center justify-center gap-1 rounded-lg px-2 text-gray-500
                   transition-all duration-150
                   hover:bg-sky-50 hover:text-sky-600
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500">
            <img src="/images/icons/view.svg" alt="" class="h-4 w-4" aria-hidden="true">
            <span class="text-xs font-medium">{{ __('keywords.view') ?? 'View' }}</span>
        </a>
    @endif

    @if ($editAction && $canEdit)
        <button wire:click="{{ $editAction }}" title="{{ __('keywords.edit') ?? 'Edit' }}"
            aria-label="{{ __('keywords.edit') ?? 'Edit' }}"
            class="inline-flex h-8 items-center justify-center gap-1 rounded-lg px-2 text-gray-500
                   transition-all duration-150
                   hover:bg-emerald-50 hover:text-emerald-600
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
            <img src="/images/icons/edit.svg" alt="" class="h-4 w-4" aria-hidden="true">
            <span class="text-xs font-medium">{{ __('keywords.edit') ?? 'Edit' }}</span>
        </button>
    @endif

    @if ($deleteAction && $canDelete)
        <button wire:click="{{ $deleteAction }}" title="{{ __('keywords.delete') ?? 'Delete' }}"
            aria-label="{{ __('keywords.delete') ?? 'Delete' }}"
            class="inline-flex h-8 items-center justify-center gap-1 rounded-lg px-2 text-gray-500
                   transition-all duration-150
                   hover:bg-red-50 hover:text-red-600
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-400">
            <img src="/images/icons/delete.svg" alt="" class="h-4 w-4" aria-hidden="true">
            <span class="text-xs font-medium">{{ __('keywords.delete') ?? 'Delete' }}</span>
        </button>
    @endif
</div>
