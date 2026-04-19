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
        ['key' => 'code', 'label' => __('keywords.code')],
        ['key' => 'name', 'label' => __('keywords.name')],
        ['key' => 'address', 'label' => __('keywords.address')],
        ['key' => 'phone', 'label' => __('keywords.phone')],
        ['key' => 'national_number', 'label' => __('keywords.national_number')],
        ['key' => 'place', 'label' => __('keywords.place')],
        ['key' => 'balance', 'label' => __('keywords.balance')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->customers as $customer)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500">{{ $customer->code ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $customer->name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span
                        class="text-sm text-gray-500 truncate max-w-50 inline-block">{{ $customer->address ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500">
                        {{ $customer->phone_numbers !== [] ? implode(' - ', $customer->phone_numbers) : '—' }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500">{{ $customer->national_number ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500">{{ $customer->place?->name ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span @class([
                        'text-sm font-medium',
                        'text-purple-600' => $customer->balance >= 0,
                        'text-red-600' => $customer->balance < 0,
                    ])>
                        {{ number_format($customer->balance, 2) }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions :viewUrl="route('customers.view', $customer->slug)" editAction="openEdit({{ $customer->id }})" :canEdit="auth()->user()->can('manage_customers')"
                        :canDelete="auth()->user()->can('manage_customers')" deleteAction="setDelete({{ $customer->id }})" />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_customers_found')" :colspan="8" />
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
                    <x-input name="form.code" label="{{ __('keywords.code') }}"
                        placeholder="{{ __('keywords.enter_code') }}" wire:model.blur="form.code" />
                    <x-select name="form.place_id" label="{{ __('keywords.places') }}" wire:model.blur="form.place_id"
                        :options="$this->placeOptions" placeholder="{{ __('keywords.select_place') }}" required />
                    <button type="button" wire:click="openCreatePlaceModal"
                        class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                        <i class="fas fa-location-dot me-1"></i>
                        {{ __('keywords.add_place') }}
                    </button>
                    <x-phone-repeater name="form.phones" :label="__('keywords.phone')" :placeholder="__('keywords.enter_your_phone')" />
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
                    <x-input name="form.code" label="{{ __('keywords.code') }}"
                        placeholder="{{ __('keywords.enter_code') }}" wire:model.blur="form.code" />
                    <x-select name="form.place_id" label="{{ __('keywords.places') }}" wire:model.blur="form.place_id"
                        :options="$this->placeOptions" placeholder="{{ __('keywords.select_place') }}" required />
                    <button type="button" wire:click="openCreatePlaceModal"
                        class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                        <i class="fas fa-location-dot me-1"></i>
                        {{ __('keywords.add_place') }}
                    </button>
                    <x-phone-repeater name="form.phones" :label="__('keywords.phone')" :placeholder="__('keywords.enter_your_phone')" />
                    <x-input name="form.national_number" label="{{ __('keywords.national_number') }}"
                        placeholder="{{ __('keywords.enter_national_number') }}" wire:model.blur="form.national_number" />
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

        <x-modal name="create-place-inline" title="{{ __('keywords.create_place') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-4">
                    <x-input name="newPlace.name" label="{{ __('keywords.name') }}"
                        placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="newPlace.name" required />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary" @click="$dispatch('close-modal-create-place-inline')">
                    {{ __('keywords.cancel') }}
                </x-button>
                <x-button variant="primary" wire:click="createPlaceInline">{{ __('keywords.add_place') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Delete Confirmation Modal --}}
        <x-confirm-modal name="delete-customer" title="{{ __('keywords.delete_customer') }}"
            message="{{ __('keywords.delete_customer_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan
</div>
