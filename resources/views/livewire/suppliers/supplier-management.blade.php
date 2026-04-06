<div x-on:confirmed-delete-supplier.window="$wire.delete()">
    <x-page-header :title="__('keywords.suppliers')" :description="__('keywords.suppliers_management')">
        @can('manage_suppliers')
            <x-slot:actions>
                <x-button variant="primary" @click="$dispatch('open-modal-create-supplier')">
                    <i class="fas fa-plus text-xs"></i>
                    {{ __('keywords.add_supplier') }}
                </x-button>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-search-toolbar />

    {{-- Suppliers table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'name', 'label' => __('keywords.name')],
        ['key' => 'phone', 'label' => __('keywords.phone')],
        ['key' => 'balance', 'label' => __('keywords.balance')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->suppliers as $supplier)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $supplier->name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500">
                        {{ $supplier->phone_numbers !== [] ? implode(' - ', $supplier->phone_numbers) : '—' }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span @class([
                        'text-sm font-medium',
                        'text-purple-600' => $supplier->balance >= 0,
                        'text-red-600' => $supplier->balance < 0,
                    ])>
                        {{ number_format($supplier->balance, 2) }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('suppliers.view', $supplier) }}"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-sky-50 hover:text-sky-600 transition-colors"
                            title="{{ __('keywords.view') }}">
                            <i class="fas fa-eye text-sm"></i>
                        </a>
                        @can('manage_suppliers')
                            <button type="button" wire:click="openEdit({{ $supplier->id }})"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-sky-50 hover:text-sky-600 transition-colors"
                                title="{{ __('keywords.edit') }}">
                                <i class="fas fa-pen-to-square text-sm"></i>
                            </button>
                            <button type="button" wire:click="setDelete({{ $supplier->id }})"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                                title="{{ __('keywords.delete') }}">
                                <i class="fas fa-trash-can text-sm"></i>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_suppliers_found')" :colspan="4" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->suppliers" />

    @can('manage_suppliers')
        {{-- Create supplier Modal --}}
        <x-modal name="create-supplier" title="{{ __('keywords.create_supplier') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="form.name" label="{{ __('keywords.name') }}"
                        placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />
                    <x-phone-repeater name="form.phones" :label="__('keywords.phone')" :placeholder="__('keywords.enter_your_phone')" />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-create-supplier')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="create">{{ __('keywords.add') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Edit supplier Modal --}}
        <x-modal name="edit-supplier" title="{{ __('keywords.edit_supplier') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="form.name" label="{{ __('keywords.name') }}"
                        placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />
                    <x-phone-repeater name="form.phones" :label="__('keywords.phone')" :placeholder="__('keywords.enter_your_phone')" />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-edit-supplier')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="updateSupplier">{{ __('keywords.update') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Delete Confirmation Modal --}}
        <x-confirm-modal name="delete-supplier" title="{{ __('keywords.delete_supplier') }}"
            message="{{ __('keywords.delete_supplier_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan
</div>
