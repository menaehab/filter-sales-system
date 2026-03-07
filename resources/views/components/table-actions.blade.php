@props([
    'editAction' => null,
    'deleteAction' => null,
    'viewUrl' => null,
    'canView' => true,
    'canEdit' => true,
    'canDelete' => true,

])

<div class="flex items-center justify-end gap-1">
    @if($viewUrl && $canView)
        <a href="{{ $viewUrl }}"
            class="rounded-lg p-1.5 text-gray-400 hover:bg-sky-50 hover:text-sky-600 transition-colors"
            title="{{ __('keywords.view') ?? 'View' }}">
            <i class="fas fa-eye text-sm"></i>
        </a>
    @endif

    @if($editAction && $canEdit)
        <button wire:click="{{ $editAction }}"
            class="rounded-lg p-1.5 text-gray-400 hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
            title="{{ __('keywords.edit') ?? 'Edit' }}">
            <i class="fas fa-pen-to-square text-sm"></i>
        </button>
    @endif

    @if($deleteAction && $canDelete)
        <button wire:click="{{ $deleteAction }}"
            class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors"
            title="{{ __('keywords.delete') ?? 'Delete' }}">
            <i class="fas fa-trash-can text-sm"></i>
        </button>
    @endif
</div>
