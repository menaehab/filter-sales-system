<div x-on:confirmed-delete-filter.window="$wire.delete()">
    <x-page-header :title="__('keywords.filters')" :description="__('keywords.filters_management')">
        @can('manage_water_filters')
            <x-slot:actions>
                <x-button variant="primary" @click="$dispatch('open-modal-create-filter')">
                    <i class="fas fa-plus text-xs"></i>
                    {{ __('keywords.add_filter') }}
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
                const all = @js($this->customers->map(fn($c) => ['name' => $c->name, 'slug' => $c->slug, 'phone' => $c->phone_numbers !== [] ? implode(' - ', $c->phone_numbers) : '—'])->toArray());
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
            },
            clear() {
                this.search = '';
                this.selected = '';
            }
        }" class="relative w-full sm:max-w-xs">
            <input type="text" x-model="search" @focus="open = true" @click="open = true" @click.outside="open = false"
                placeholder="{{ __('keywords.search_customer') }}"
                class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />

            <button x-show="selected" @click="clear()" type="button"
                class="absolute inset-e-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xs"></i>
            </button>

            <input type="hidden" wire:model.live="customerSlug" />

            <div x-show="open" x-cloak
                class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                <div class="max-h-52 overflow-y-auto">
                    <template x-for="customer in filtered" :key="customer.slug">
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
    </x-search-toolbar>

    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm font-semibold text-gray-900">{{ __('keywords.candle_needs_replacement') }}</p>

            @if (!empty($candleNeedsReplacement))
                <button type="button" wire:click="clearCandleNeedsReplacement"
                    class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    {{ __('keywords.clear') }}
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            @foreach ($this->candleFilterOptions as $candleKey => $candleLabel)
                <label
                    class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-50">
                    <input type="checkbox" value="{{ $candleKey }}" wire:model.live="candleNeedsReplacement"
                        class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="font-medium">{{ $candleLabel }}</span>
                </label>
            @endforeach
        </div>
    </div>

    {{-- Filters table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'filter_model', 'label' => __('keywords.filter_model')],
        ['key' => 'address', 'label' => __('keywords.address')],
        ['key' => 'customer', 'label' => __('keywords.customer')],
        ['key' => 'phone', 'label' => __('keywords.phone')],
        ['key' => 'is_installed', 'label' => __('keywords.installation_status')],
        ['key' => 'installed_at', 'label' => __('keywords.installed_at')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->filters as $filter)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $filter->filter_model }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-700">{{ $filter->address }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-900">{{ $filter->customer?->name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span
                        class="text-sm text-gray-500">{{ $filter->customer?->phone_numbers !== [] ? implode(' - ', $filter->customer->phone_numbers) : '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-700">
                        {{ $filter->is_installed ? __('keywords.yes') : __('keywords.no') }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-500">
                        {{ $filter->is_installed ? $filter->installed_at?->format('Y-m-d') ?? '—' : '—' }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('filters.view', $filter) }}"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-sky-50 hover:text-sky-600 transition-colors"
                            title="{{ __('keywords.view') }}">
                            <i class="fas fa-eye text-sm"></i>
                        </a>
                        @can('manage_water_filters')
                            <button type="button" wire:click="openEdit({{ $filter->id }})"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-sky-50 hover:text-sky-600 transition-colors"
                                title="{{ __('keywords.edit') }}">
                                <i class="fas fa-pen-to-square text-sm"></i>
                            </button>
                            <button type="button" wire:click="setDelete({{ $filter->id }})"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                                title="{{ __('keywords.delete') }}">
                                <i class="fas fa-trash-can text-sm"></i>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_filters_found')" :colspan="7" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->filters" />

    @can('manage_water_filters')
        {{-- Create filter Modal --}}
        <x-modal name="create-filter" title="{{ __('keywords.create_filter') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="form.filter_model" label="{{ __('keywords.filter_model') }}"
                        placeholder="{{ __('keywords.enter_filter_model') }}" wire:model.blur="form.filter_model"
                        required />

                    <x-input name="form.address" label="{{ __('keywords.address') }}"
                        placeholder="{{ __('keywords.enter_address') }}" wire:model.blur="form.address" required />

                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" wire:model.live="form.is_installed"
                            class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span>{{ __('keywords.is_installed') }}</span>
                    </label>
                    @error('form.is_installed')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    @if ($form['is_installed'] ?? false)
                        <x-input type="date" name="form.installed_at" label="{{ __('keywords.installed_at') }}"
                            wire:model.blur="form.installed_at" required />
                    @endif

                    <div x-data="{
                        open: false,
                        search: @entangle('customerModalSearch'),
                        selected: @entangle('form.customer_id'),
                        customers: @js($this->customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone_numbers !== [] ? implode(' - ', $c->phone_numbers) : '—'])->toArray()),
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
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            {{ __('keywords.customer') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="search" @focus="open = true" @click="open = true"
                            @click.outside="open = false" placeholder="{{ __('keywords.search_customer') }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                        <input type="hidden" wire:model="form.customer_id" />
                        @error('form.customer_id')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div x-show="open" x-cloak
                            class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
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
                    @click="$dispatch('close-modal-create-filter')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="create">{{ __('keywords.add') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Edit filter Modal --}}
        <x-modal name="edit-filter" title="{{ __('keywords.edit_filter') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="form.filter_model" label="{{ __('keywords.filter_model') }}"
                        placeholder="{{ __('keywords.enter_filter_model') }}" wire:model.blur="form.filter_model"
                        required />

                    <x-input name="form.address" label="{{ __('keywords.address') }}"
                        placeholder="{{ __('keywords.enter_address') }}" wire:model.blur="form.address" required />

                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" wire:model.live="form.is_installed"
                            class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span>{{ __('keywords.is_installed') }}</span>
                    </label>
                    @error('form.is_installed')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    @if ($form['is_installed'] ?? false)
                        <x-input type="date" name="form.installed_at" label="{{ __('keywords.installed_at') }}"
                            wire:model.blur="form.installed_at" required />
                    @endif

                    <div x-data="{
                        open: false,
                        search: @entangle('customerModalSearch'),
                        selected: @entangle('form.customer_id'),
                        customers: @js($this->customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone_numbers !== [] ? implode(' - ', $c->phone_numbers) : '—'])->toArray()),
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
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            {{ __('keywords.customer') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="search" @focus="open = true" @click="open = true"
                            @click.outside="open = false" placeholder="{{ __('keywords.search_customer') }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                        <input type="hidden" wire:model="form.customer_id" />
                        @error('form.customer_id')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div x-show="open" x-cloak
                            class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
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
                    @click="$dispatch('close-modal-edit-filter')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="updateFilter">{{ __('keywords.update') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Delete Confirmation Modal --}}
        <x-confirm-modal name="delete-filter" title="{{ __('keywords.delete_filter') }}"
            message="{{ __('keywords.delete_filter_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan
</div>
