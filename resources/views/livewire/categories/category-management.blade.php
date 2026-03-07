<div x-on:confirmed-delete-category.window="$wire.delete()">
    <x-page-header :title="__('keywords.categories')" :description="__('keywords.categories_management')">
        <x-slot:actions>
            <x-button variant="primary" @click="$dispatch('open-modal-create-category')">
                <i class="fas fa-plus text-xs"></i>
                {{ __('keywords.add_category') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-search-toolbar />

    {{-- Categories table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'name', 'label' => __('keywords.name')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->categories as $category)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $category->name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions
                        editAction="openEdit({{ $category->id }})"
                        deleteAction="setDelete({{ $category->id }})"
                    />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_categories_found')" :colspan="2" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->categories" />

    {{-- Create Category Modal --}}
    <x-modal name="create-category" title="{{ __('keywords.create_category') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                <x-input name="form.name" label="{{ __('keywords.name') }}"
                    placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary"
                @click="$dispatch('close-modal-create-category')">{{ __('keywords.cancel') }}</x-button>
            <x-button variant="primary" wire:click="create">{{ __('keywords.add') }}</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Edit Category Modal --}}
    <x-modal name="edit-category" title="{{ __('keywords.edit_category') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                <x-input name="form.name" label="{{ __('keywords.name') }}"
                    placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary"
                @click="$dispatch('close-modal-edit-category')">{{ __('keywords.cancel') }}</x-button>
            <x-button variant="primary" wire:click="updateCategory">{{ __('keywords.update') }}</x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-modal name="delete-category" title="{{ __('keywords.delete_category') }}"
        message="{{ __('keywords.delete_category_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
        variant="danger" />
</div>
