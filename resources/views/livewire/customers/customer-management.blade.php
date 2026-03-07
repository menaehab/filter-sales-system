<div x-on:confirmed-delete-customer.window="$wire.delete()">
    <x-page-header :title="__('keywords.customers')" :description="__('keywords.customers_management')">
        @can('manage_customers')
            <x-slot:actions>
                <x-button variant="primary" @click="$dispatch('open-modal-create-customer')">
                    <i class="fas fa-plus text-xs"></i>
                    {{ __('keywords.add_customer') }}
                </x-button>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-search-toolbar />

    {{-- Customers table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'name', 'label' => __('keywords.name')],
        ['key' => 'phone', 'label' => __('keywords.phone')],
        ['key' => 'national_number', 'label' => __('keywords.national_number')],
        ['key' => 'address', 'label' => __('keywords.address')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->customers as $customer)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $customer->name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500">{{ $customer->phone ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500">{{ $customer->national_number ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500 truncate max-w-[200px] inline-block">{{ $customer->address ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions
                        :viewUrl="route('customers.show', $customer->slug)"
                        editAction="openEdit({{ $customer->id }})"
                        :canEdit="auth()->user()->can('manage_customers')"
                        :canDelete="auth()->user()->can('manage_customers')"
                        deleteAction="setDelete({{ $customer->id }})"
                    />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_customers_found')" :colspan="5" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->customers" />

    @can('manage_customers')
        {{-- Create customer Modal --}}
        <x-modal name="create-customer" title="{{ __('keywords.create_customer') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="form.name" label="{{ __('keywords.name') }}"
                        placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />
                    <x-input name="form.phone" label="{{ __('keywords.phone') }}"
                        placeholder="{{ __('keywords.enter_your_phone') }}" wire:model.blur="form.phone" />
                    <x-input name="form.national_number" label="{{ __('keywords.national_number') }}"
                        placeholder="{{ __('keywords.enter_national_number') }}" wire:model.blur="form.national_number" />
                    <x-input name="form.address" label="{{ __('keywords.address') }}"
                        placeholder="{{ __('keywords.enter_address') }}" wire:model.blur="form.address" />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-create-customer')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="create">{{ __('keywords.add') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Edit customer Modal --}}
        <x-modal name="edit-customer" title="{{ __('keywords.edit_customer') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="form.name" label="{{ __('keywords.name') }}"
                        placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />
                    <x-input name="form.phone" label="{{ __('keywords.phone') }}"
                        placeholder="{{ __('keywords.enter_your_phone') }}" wire:model.blur="form.phone" />
                    <x-input name="form.national_number" label="{{ __('keywords.national_number') }}"
                        placeholder="{{ __('keywords.enter_national_number') }}"
                        wire:model.blur="form.national_number" />
                    <x-input name="form.address" label="{{ __('keywords.address') }}"
                        placeholder="{{ __('keywords.enter_address') }}" wire:model.blur="form.address" />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-edit-customer')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="updateCustomer">{{ __('keywords.update') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Delete Confirmation Modal --}}
        <x-confirm-modal name="delete-customer" title="{{ __('keywords.delete_customer') }}"
            message="{{ __('keywords.delete_customer_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan
</div>
