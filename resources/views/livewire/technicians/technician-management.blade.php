<div x-on:confirmed-delete-technician.window="$wire.delete()">
    <x-page-header :title="__('keywords.technicians')" :description="__('keywords.technicians_management')">
        <x-slot:actions>
            @can('manage_technicians')
                <x-button variant="primary" wire:click="openCreateModal('open-modal-create-technician')">
                    <i class="fas fa-plus text-xs"></i>
                    {{ __('keywords.add_technician') }}
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-search-toolbar />

    {{-- Technicians table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'name', 'label' => __('keywords.technician_name')],
        ['key' => 'phone', 'label' => __('keywords.phone')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse($this->technicians as $technician)
            <tr wire:key="technician-{{ $technician->id }}" class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">
                    {{ $technician->name }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                    {{ $technician->phone ?? '—' }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions 
                        viewUrl="{{ route('technicians.view', $technician) }}"
                        editAction="openEdit({{ $technician->id }})"
                        deleteAction="setDelete({{ $technician->id }})" 
                    />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_technicians_found')" :colspan="3" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->technicians" />

    {{-- Create Technician Modal --}}
    <x-modal name="create-technician" title="{{ __('keywords.add_technician') }}" maxWidth="md">
        <x-slot:body>
            <div class="space-y-4">
                <x-input wire:model="form.name" name="form.name" label="{{ __('keywords.technician_name') }}" required />
                <x-input wire:model="form.phone" name="form.phone" label="{{ __('keywords.phone') }}" dir="ltr" />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary" @click="$dispatch('close-modal-create-technician')">
                {{ __('keywords.cancel') }}
            </x-button>
            <x-button variant="primary" wire:click="create" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="create">{{ __('keywords.save') }}</span>
                <span wire:loading wire:target="create">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Edit Technician Modal --}}
    <x-modal name="edit-technician" title="{{ __('keywords.edit_technician') }}" maxWidth="md">
        <x-slot:body>
            <div class="space-y-4">
                <x-input wire:model="form.name" name="form.name" label="{{ __('keywords.technician_name') }}" required />
                <x-input wire:model="form.phone" name="form.phone" label="{{ __('keywords.phone') }}" dir="ltr" />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary" @click="$dispatch('close-modal-edit-technician')">
                {{ __('keywords.cancel') }}
            </x-button>
            <x-button variant="primary" wire:click="update" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="update">{{ __('keywords.save') }}</span>
                <span wire:loading wire:target="update">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-modal name="delete-technician" 
        title="{{ __('keywords.confirm_delete') }}"
        message="{{ __('keywords.delete_confirmation_message') }}"
        confirmText="{{ __('keywords.delete') }}"
        variant="danger" 
    />
</div>
