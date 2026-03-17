<div x-on:confirmed-delete-water-reading.window="$wire.delete()">
    <x-page-header :title="__('keywords.view_water_readings')" :description="__('keywords.water_readings_management')">
        @can('manage_water_readings')
            <x-slot:actions>
                <x-button variant="primary" @click="$dispatch('open-modal-create-water-reading')">
                    {{ __('keywords.add_water_reading') }}
                </x-button>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-search-toolbar>
        <div x-data="{
                open: false,
                search: @entangle('customerSearch'),
                selected: @entangle('customerSlug'),
                get filtered() {
                    const all = @js($this->customers->map(fn($c) => ['name' => $c->name, 'slug' => $c->slug, 'phone' => $c->phone])->toArray());
                    const query = this.search.toLowerCase().trim();

                    if (!query) {
                        return all.slice(0, 50);
                    }

                    return all.filter(c => c.name.toLowerCase().includes(query));
                },
                select(customer) {
                    this.search = customer.name;
                    this.selected = customer.slug;
                    this.open = false;
                }
            }"
            class="relative w-full sm:max-w-xs">
            <input type="text" x-model="search" @focus="open = true" @click="open = true"
                @click.outside="open = false" placeholder="{{ __('keywords.search_customer') }}"
                class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />

            <input type="hidden" wire:model.live="customerSlug" />

            <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                <div class="max-h-52 overflow-y-auto">
                    <template x-for="customer in filtered" :key="customer.slug">
                        <button type="button" @click="select(customer)" class="block w-full px-3 py-2 text-start text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">
                            <div class="flex items-center justify-between">
                                <span x-text="customer.name"></span>
                                <span class="text-xs text-gray-500" x-text="customer.phone"></span>
                            </div>
                        </button>
                    </template>
                    <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500">
                        {{ __('keywords.no_customers_found') }}
                    </div>
                </div>
            </div>
        </div>

        <x-select name="waterQuality" wire:model.live="waterQuality" :options="collect($this->waterQualityOptions)->mapWithKeys(fn($q) => [$q->value => __('keywords.' . $q->label())])->toArray()"
            placeholder="{{ __('keywords.water_quality') }}" class="min-w-37.5" />
    </x-search-toolbar>

    {{-- water_readings table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'technician_name', 'label' => __('keywords.technician_name')],
        ['key' => 'customer', 'label' => __('keywords.customer')],
            ['key' => 'phone', 'label' => __('keywords.phone')],
            ['key' => 'tds', 'label' => __('keywords.tds')],
            ['key' => 'water_quality', 'label' => __('keywords.water_quality')],
            ['key' => 'created_at', 'label' => __('keywords.date')],
            ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
        ]">
            @forelse ($this->water_readings as $water_reading)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="text-sm font-medium text-gray-900">{{ $water_reading->technician_name }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="text-sm text-gray-900">{{ $water_reading->customer?->name }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="text-sm text-gray-900">{{ $water_reading->customer?->phone }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="text-sm text-gray-900">{{ $water_reading->tds }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="text-sm text-gray-900">{{ __('keywords.' . $water_reading->water_quality) }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="text-sm text-gray-900">{{ $water_reading->created_at?->format('Y-m-d') }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                        <x-table-actions editAction="openEdit({{ $water_reading->id }})" :canEdit="auth()->user()->can('manage_water_readings')" :canDelete="auth()->user()->can('manage_water_readings')"
                            deleteAction="setDelete({{ $water_reading->id }})" />
                    </td>
                </tr>
            @empty
                <x-empty-state :title="__('keywords.no_water_readings_found')" :colspan="7" />
            @endforelse
        </x-data-table>

        <x-pagination-info :paginator="$this->water_readings" />
    @can('manage_water_readings')
        {{-- Create water_reading Modal --}}
        <x-modal name="create-water-reading" title="{{ __('keywords.create_water_reading') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="form.technician_name" label="{{ __('keywords.technician_name') }}"
                        placeholder="{{ __('keywords.enter_technician_name') }}" wire:model.blur="form.technician_name" required />

                    <x-input type="number" name="form.tds" label="{{ __('keywords.tds') }}"
                        placeholder="0" wire:model.blur="form.tds" required />

                    <x-select name="form.water_quality" label="{{ __('keywords.water_quality') }}"
                        wire:model.blur="form.water_quality"
                        :options="collect($this->waterQualityOptions)->mapWithKeys(fn($q) => [$q->value => __('keywords.' . $q->label())])->toArray()"
                        placeholder="{{ __('keywords.select_water_quality') }}" required />

                    <div x-data="{
                            open: false,
                            search: @entangle('customerModalSearch'),
                            selected: @entangle('form.customer_id'),
                            customers: @js($this->customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone])->toArray()),
                            get filtered() {
                                const query = (this.search || '').toString().toLowerCase().trim();
                                if (!query) {
                                    return this.customers.slice(0, 50);
                                }
                                return this.customers.filter(c => c.name.toLowerCase().includes(query));
                            },
                            select(customer) {
                                this.selected = customer.id;
                                this.search = customer.name;
                                this.open = false;
                            },
                        }" class="relative">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('keywords.customer') }}</label>
                        <input type="text" x-model="search" @focus="open = true" @click="open = true"
                            @click.outside="open = false" placeholder="{{ __('keywords.search_customer') }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                        <input type="hidden" wire:model="form.customer_id" />
                        <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                            <div class="max-h-52 overflow-y-auto">
                                <template x-for="customer in filtered" :key="customer.id">
                                    <button type="button" @click="select(customer)"
                                        class="block w-full px-3 py-2 text-start text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">
                                        <div class="flex items-center justify-between">
                                            <span x-text="customer.name"></span>
                                            <span class="text-xs text-gray-500" x-text="customer.phone"></span>
                                        </div>
                                    </button>
                                </template>
                                <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500">
                                    {{ __('keywords.no_customers_found') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-create-water-reading')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="create">{{ __('keywords.add') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Edit water_reading Modal --}}
        <x-modal name="edit-water-reading" title="{{ __('keywords.edit_water_reading') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="form.technician_name" label="{{ __('keywords.technician_name') }}"
                        placeholder="{{ __('keywords.enter_technician_name') }}" wire:model.blur="form.technician_name" required />

                    <x-input type="number" name="form.tds" label="{{ __('keywords.tds') }}"
                        placeholder="0" wire:model.blur="form.tds" required />

                    <x-select name="form.water_quality" label="{{ __('keywords.water_quality') }}"
                        wire:model.blur="form.water_quality"
                        :options="collect($this->waterQualityOptions)->mapWithKeys(fn($q) => [$q->value => __('keywords.' . $q->label())])->toArray()"
                        placeholder="{{ __('keywords.select_water_quality') }}" required />

                    <div x-data="{
                            open: false,
                            search: @entangle('customerModalSearch'),
                            selected: @entangle('form.customer_id'),
                            customers: @js($this->customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone])->toArray()),
                            get filtered() {
                                const query = (this.search || '').toString().toLowerCase().trim();
                                if (!query) {
                                    return this.customers.slice(0, 50);
                                }
                                return this.customers.filter(c => c.name.toLowerCase().includes(query));
                            },
                            select(customer) {
                                this.selected = customer.id;
                                this.search = customer.name;
                                this.open = false;
                            },
                        }" class="relative">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('keywords.customer') }}</label>
                        <input type="text" x-model="search" @focus="open = true" @click="open = true"
                            @click.outside="open = false" placeholder="{{ __('keywords.search_customer') }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                        <input type="hidden" wire:model="form.customer_id" />
                        <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                            <div class="max-h-52 overflow-y-auto">
                                <template x-for="customer in filtered" :key="customer.id">
                                    <button type="button" @click="select(customer)"
                                        class="block w-full px-3 py-2 text-start text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">
                                        <div class="flex items-center justify-between">
                                            <span x-text="customer.name"></span>
                                            <span class="text-xs text-gray-500" x-text="customer.phone"></span>
                                        </div>
                                    </button>
                                </template>
                                <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500">
                                    {{ __('keywords.no_customers_found') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-edit-water-reading')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="updateWaterReading">{{ __('keywords.update') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Delete Confirmation Modal --}}
        <x-confirm-modal name="delete-water-reading" title="{{ __('keywords.delete_water_reading') }}"
            message="{{ __('keywords.delete_water_reading_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan

</div>
