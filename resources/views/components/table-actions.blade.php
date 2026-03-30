@props([
    'editAction' => null,
    'deleteAction' => null,
    'viewUrl' => null,
    'canView' => true,
    'canEdit' => true,
    'canDelete' => true,
])

<div class="flex items-center justify-end gap-1.5">
    @if ($viewUrl && $canView)
        <a href="{{ $viewUrl }}"
            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-gray-400 transition-all duration-150 hover:bg-sky-50 hover:text-sky-600"
            title="{{ __('keywords.view') ?? 'View' }}">
            <i class="fas fa-eye text-sm"></i>
        </a>
    @endif

    @if ($editAction && $canEdit)
        <button wire:click="{{ $editAction }}"
            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-gray-400 transition-all duration-150 hover:bg-emerald-50 hover:text-emerald-600"
            title="{{ __('keywords.edit') ?? 'Edit' }}">
            <i class="fas fa-pen-to-square text-sm"></i>
        </button>
    @endif

    @if ($deleteAction && $canDelete)
        <button wire:click="{{ $deleteAction }}"
            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-gray-400 transition-all duration-150 hover:bg-red-50 hover:text-red-600"
            title="{{ __('keywords.delete') ?? 'Delete' }}">
            <i class="fas fa-trash-can text-sm"></i>
        </button>
    @endif
</div>
