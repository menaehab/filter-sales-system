<div x-on:confirmed-delete-customer.window="$wire.delete()">
    {{-- Page header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ __('keywords.customers') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('keywords.customers_management') }}</p>
        </div>
        <x-button variant="primary" @click="$dispatch('open-modal-create-customer')">
            <svg class="-ms-0.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            {{ __('keywords.add_customer') }}
        </x-button>
    </div>

    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-xs">
            <svg class="pointer-events-none absolute inset-s-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder={{ __('keywords.search') }}
                class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-10 pe-4 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
        </div>

        <select wire:model.live="perPage"
            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 sm:w-auto">
            <option value="10">{{ __('keywords.per_page', ['count' => 10]) }}</option>
            <option value="25">{{ __('keywords.per_page', ['count' => 25]) }}</option>
            <option value="50">{{ __('keywords.per_page', ['count' => 50]) }}</option>
            <option value="100">{{ __('keywords.per_page', ['count' => 100]) }}</option>
        </select>
    </div>

    {{-- customers table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'name', 'label' => __('keywords.name')],
        ['key' => 'phone', 'label' => __('keywords.phone')],
        ['key' => 'national_number', 'label' => __('keywords.national_number')],
        ['key' => 'address', 'label' => __('keywords.address')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">

        @forelse ($this->customers as $customer)
            <tr class="hover:bg-gray-50">
                {{-- customer info --}}
                <td class="whitespace-nowrap px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-900">{{ $customer->name }}</span>
                    </div>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-900">{{ $customer->phone }}</span>
                    </div>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-900">{{ $customer->national_number }}</span>
                    </div>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-900">{{ $customer->address }}</span>
                    </div>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <div class="flex items-center justify-end gap-2">
                        <a href="#" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-blue-600"
                            title="View">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </a>
                        <button class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-emerald-600"
                            title="Edit" wire:click="openEdit({{ $customer->id }})">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                        <button class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600" title="Delete"
                            wire:click="setDelete({{ $customer->id }})">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                    {{ __('keywords.no_customers_found') }}</td>
            </tr>
        @endforelse
    </x-data-table>

    <div class="mt-4 flex flex-col items-center justify-between gap-4 sm:flex-row">
        <p class="text-sm text-gray-500">
            {{ __('keywords.showing') }} <span
                class="font-medium text-gray-700">{{ $this->customers->firstItem() ?? 0 }}</span>
            {{ __('keywords.to') }} <span
                class="font-medium text-gray-700">{{ $this->customers->lastItem() ?? 0 }}</span>
            {{ __('keywords.of') }} <span class="font-medium text-gray-700">{{ $this->customers->total() }}</span>
            {{ __('keywords.results') }}
        </p>
        <div>
            {{ $this->customers->links() }}
        </div>
    </div>

    {{-- Create customer Modal --}}
    <x-modal name="create-customer" title="{{ __('keywords.create_customer') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                <x-input name="form.name" label="{{ __('keywords.name') }}"
                    placeholder="{{ __('keywords.enter_name') }}" wire:model.live="form.name" required />
                <x-input name="form.phone" label="{{ __('keywords.phone') }}"
                    placeholder="{{ __('keywords.enter_your_phone') }}" wire:model.live="form.phone" />
                <x-input name="form.national_number" label="{{ __('keywords.national_number') }}"
                    placeholder="{{ __('keywords.enter_national_number') }}" wire:model.live="form.national_number" />
                <x-input name="form.address" label="{{ __('keywords.address') }}"
                    placeholder="{{ __('keywords.enter_address') }}" wire:model.live="form.address" />
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
                    placeholder="{{ __('keywords.enter_name') }}" wire:model.live="form.name" required />
                <x-input name="form.phone" label="{{ __('keywords.phone') }}"
                    placeholder="{{ __('keywords.enter_your_phone') }}" wire:model.live="form.phone" />
                <x-input name="form.national_number" label="{{ __('keywords.national_number') }}"
                    placeholder="{{ __('keywords.enter_national_number') }}"
                    wire:model.live="form.national_number" />
                <x-input name="form.address" label="{{ __('keywords.address') }}"
                    placeholder="{{ __('keywords.enter_address') }}" wire:model.live="form.address" />
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
</div>
