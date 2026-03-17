<div x-on:confirmed-delete-damaged-product.window="$wire.delete()">
    <x-page-header :title="__('keywords.view_damaged_products')" :description="__('keywords.damaged_products_management')">
        @can('manage_damaged_products')
            <x-slot:actions>
                <x-button variant="primary" @click="$dispatch('open-modal-create-damaged-product')">
                    {{ __('keywords.add_damaged_product') }}
                </x-button>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-search-toolbar>
        <div x-data="{
                open: false,
                search: @entangle('productSearch'),
                selected: @entangle('productSlug'),
                get filtered() {
                    const all = @js($this->products->map(fn($p) => ['name' => $p->name, 'slug' => $p->slug, 'category' => $p->category?->name])->toArray());
                    const query = this.search.toLowerCase().trim();

                    if (!query) {
                        return all.slice(0, 50);
                    }

                    return all.filter(p => p.name.toLowerCase().includes(query));
                },
                select(product) {
                    this.search = product.name;
                    this.selected = product.slug;
                    this.open = false;
                }
            }"
            class="relative w-full sm:max-w-xs">
            <input type="text" x-model="search" @focus="open = true" @click="open = true"
                @click.outside="open = false" placeholder="{{ __('keywords.search_product') }}"
                class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />

            <input type="hidden" wire:model.live="productSlug" />

            <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                <div class="max-h-52 overflow-y-auto">
                    <template x-for="product in filtered" :key="product.slug">
                        <button type="button" @click="select(product)" class="block w-full px-3 py-2 text-start text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">
                            <div class="flex items-center justify-between">
                                <span x-text="product.name"></span>
                                <span class="text-xs text-gray-500" x-text="product.category"></span>
                            </div>
                        </button>
                    </template>
                    <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500">
                        {{ __('keywords.no_products_found_search') }}
                    </div>
                </div>
            </div>
        </div>
    </x-search-toolbar>

    {{-- damaged_products table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'product', 'label' => __('keywords.product')],
        ['key' => 'quantity', 'label' => __('keywords.quantity')],
        ['key' => 'cost_price', 'label' => __('keywords.cost_price')],
        ['key' => 'reason', 'label' => __('keywords.reason')],
        ['key' => 'user', 'label' => __('keywords.user')],
        ['key' => 'created_at', 'label' => __('keywords.date')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->damaged_products as $damaged_product)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $damaged_product->product?->name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-900">{{ $damaged_product->quantity }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-900">{{ number_format($damaged_product->cost_price, 2) }} {{ __('keywords.currency') }}</span>
                </td>
                <td class="px-4 py-3 max-w-xs">
                    <span class="text-sm text-gray-900 truncate block">{{ $damaged_product->reason ?: '-' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-900">{{ $damaged_product->user?->name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-900">{{ $damaged_product->created_at?->format('Y-m-d') }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions editAction="openEdit({{ $damaged_product->id }})" :canEdit="auth()->user()->can('manage_damaged_products')" :canDelete="auth()->user()->can('manage_damaged_products')"
                        deleteAction="setDelete({{ $damaged_product->id }})" />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_damaged_products_found')" :colspan="7" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->damaged_products" />

    @can('manage_damaged_products')
        {{-- Create damaged_product Modal --}}
        <x-modal name="create-damaged-product" title="{{ __('keywords.create_damaged_product') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <div x-data="{
                            open: false,
                            search: @entangle('formProductSearch'),
                            selected: @entangle('form.product_id'),
                            products: @js($this->products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'category' => $p->category?->name, 'quantity' => $p->quantity, 'cost_price' => $p->cost_price])->toArray()),
                            get filtered() {
                                const query = (this.search || '').toString().toLowerCase().trim();
                                if (!query) {
                                    return this.products.slice(0, 50);
                                }
                                return this.products.filter(p => p.name.toLowerCase().includes(query));
                            },
                            select(product) {
                                this.selected = product.id;
                                this.search = product.name;
                                this.open = false;
                            },
                        }" class="relative">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            {{ __('keywords.product') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="search" @focus="open = true" @click="open = true"
                            @click.outside="open = false" placeholder="{{ __('keywords.search_product') }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                        <input type="hidden" wire:model="form.product_id" />
                        @error('form.product_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                            <div class="max-h-52 overflow-y-auto">
                                <template x-for="product in filtered" :key="product.id">
                                    <button type="button" @click="select(product)"
                                        class="block w-full px-3 py-2 text-start text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">
                                        <div class="flex items-center justify-between">
                                            <span x-text="product.name"></span>
                                            <span class="text-xs text-gray-500">
                                                <span x-text="product.category"></span>
                                                <span class="mx-1">|</span>
                                                <span>{{ __('keywords.quantity') }}: </span>
                                                <span x-text="product.quantity"></span>
                                            </span>
                                        </div>
                                    </button>
                                </template>
                                <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500">
                                    {{ __('keywords.no_products_found_search') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-input type="number" name="form.quantity" label="{{ __('keywords.quantity') }}"
                        placeholder="{{ __('keywords.enter_quantity') }}" wire:model.blur="form.quantity" min="1" required />

                    <x-textarea name="form.reason" label="{{ __('keywords.reason') }}"
                        placeholder="{{ __('keywords.enter_reason') }}" wire:model.blur="form.reason" rows="3" />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-create-damaged-product')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="create">{{ __('keywords.add') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Edit damaged_product Modal --}}
        <x-modal name="edit-damaged-product" title="{{ __('keywords.edit_damaged_product') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <div x-data="{
                            open: false,
                            search: @entangle('formProductSearch'),
                            selected: @entangle('form.product_id'),
                            products: @js($this->products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'category' => $p->category?->name, 'quantity' => $p->quantity, 'cost_price' => $p->cost_price])->toArray()),
                            get filtered() {
                                const query = (this.search || '').toString().toLowerCase().trim();
                                if (!query) {
                                    return this.products.slice(0, 50);
                                }
                                return this.products.filter(p => p.name.toLowerCase().includes(query));
                            },
                            select(product) {
                                this.selected = product.id;
                                this.search = product.name;
                                this.open = false;
                            },
                        }" class="relative">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            {{ __('keywords.product') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="search" @focus="open = true" @click="open = true"
                            @click.outside="open = false" placeholder="{{ __('keywords.search_product') }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" />
                        <input type="hidden" wire:model="form.product_id" />
                        @error('form.product_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                            <div class="max-h-52 overflow-y-auto">
                                <template x-for="product in filtered" :key="product.id">
                                    <button type="button" @click="select(product)"
                                        class="block w-full px-3 py-2 text-start text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">
                                        <div class="flex items-center justify-between">
                                            <span x-text="product.name"></span>
                                            <span class="text-xs text-gray-500">
                                                <span x-text="product.category"></span>
                                                <span class="mx-1">|</span>
                                                <span>{{ __('keywords.quantity') }}: </span>
                                                <span x-text="product.quantity"></span>
                                            </span>
                                        </div>
                                    </button>
                                </template>
                                <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500">
                                    {{ __('keywords.no_products_found_search') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-input type="number" name="form.quantity" label="{{ __('keywords.quantity') }}"
                        placeholder="{{ __('keywords.enter_quantity') }}" wire:model.blur="form.quantity" min="1" required />

                    <x-textarea name="form.reason" label="{{ __('keywords.reason') }}"
                        placeholder="{{ __('keywords.enter_reason') }}" wire:model.blur="form.reason" rows="3" />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-edit-damaged-product')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="updateDamagedProduct">{{ __('keywords.update') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Delete Confirmation Modal --}}
        <x-confirm-modal name="delete-damaged-product" title="{{ __('keywords.delete_damaged_product') }}"
            message="{{ __('keywords.delete_damaged_product_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan

</div>
