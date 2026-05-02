<div x-on:confirmed-delete-product.window="$wire.delete()">
    <x-page-header :title="__('keywords.products')" :description="__('keywords.products_management')">
        @can('manage_products')
            <x-slot:actions>
                <x-button variant="primary" @click="$dispatch('open-modal-create-product')">
                    {{ __('keywords.add_product') }}
                </x-button>
            </x-slot:actions>
        @endcan
    </x-page-header>

    <x-search-toolbar>
        <x-select name="categorySlug" wire:model.live="categorySlug" :options="$this->categories->pluck('name', 'slug')->toArray()"
            placeholder="{{ __('keywords.categories') }}" class="min-w-37.5" />
        <x-select name="stockStatus" wire:model.live="stockStatus" :options="[
            'above' => __('keywords.above_min_quantity'),
            'below' => __('keywords.below_min_quantity'),
        ]"
            placeholder="{{ __('keywords.stock_status') }}" class="min-w-50" />
        <x-select name="forMaintenance" wire:model.live="forMaintenance" :options="[
            'yes' => __('keywords.yes'),
            'no' => __('keywords.no'),
        ]"
            placeholder="{{ __('keywords.for_maintenance') }}" class="min-w-50" />
    </x-search-toolbar>

    {{-- Products table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'name', 'label' => __('keywords.name')],
        ['key' => 'category', 'label' => __('keywords.category')],
        ['key' => 'cost_price', 'label' => __('keywords.cost_price')],
        ['key' => 'sell_price', 'label' => __('keywords.sell_price')],
        ['key' => 'quantity', 'label' => __('keywords.quantity')],
        ['key' => 'min_quantity', 'label' => __('keywords.min_quantity')],
        ['key' => 'for_maintenance', 'label' => __('keywords.for_maintenance')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->products as $product)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $product->name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <x-badge :label="$product->category->name" color="blue" />
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                    {{ number_format($product->cost_price, 2) }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                    {{ number_format($product->sell_price, 2) }}
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span @class([
                        'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
                        'bg-red-100 text-red-700' => $product->quantity <= $product->min_quantity,
                        'bg-emerald-100 text-emerald-700' =>
                            $product->quantity > $product->min_quantity,
                    ])>
                        {{ $product->quantity }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-900">{{ $product->min_quantity }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <x-badge :label="__($product->for_maintenance ? 'keywords.yes' : 'keywords.no')" :color="$product->for_maintenance ? 'green' : 'gray'" />
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions :viewUrl="route('products.show', $product)" editAction="openEdit({{ $product->id }})" :canEdit="auth()->user()->can('manage_products')"
                        :canDelete="auth()->user()->can('manage_products')" deleteAction="setDelete({{ $product->id }})" />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_products_found')" :colspan="5" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->products" />
    @can('manage_products')
        {{-- Create Product Modal --}}
        <x-modal name="create-product" title="{{ __('keywords.create_product') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="form.name" label="{{ __('keywords.name') }}"
                        placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />
                    <x-input type="number" name="form.cost_price" label="{{ __('keywords.cost_price') }}"
                        placeholder="{{ __('keywords.enter_cost_price') }}" wire:model.blur="form.cost_price" required />
                    <x-input type="number" name="form.sell_price" label="{{ __('keywords.sell_price') }}"
                        placeholder="{{ __('keywords.enter_sell_price') }}" wire:model.blur="form.sell_price" required />
                    <x-input type="number" name="form.quantity" label="{{ __('keywords.quantity') }}"
                        placeholder="{{ __('keywords.enter_quantity') }}" wire:model.blur="form.quantity" required />
                    <x-input type="number" name="form.min_quantity" label="{{ __('keywords.min_quantity') }}"
                        placeholder="{{ __('keywords.enter_min_quantity') }}" wire:model.blur="form.min_quantity"
                        required />
                    <x-textarea name="form.description" label="{{ __('keywords.description') }}"
                        wire:model.blur="form.description" />
                    <x-select name="form.category_id" label="{{ __('keywords.category') }}"
                        wire:model.blur="form.category_id" :options="$this->categories->pluck('name', 'id')->toArray()"
                        placeholder="{{ __('keywords.select_category') }}" required />
                    <button type="button" wire:click="openCreateCategoryModal"
                        class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                        <i class="fas fa-tags me-1"></i>
                        {{ __('keywords.add_category') }}
                    </button>
                    <x-checkbox name="form.for_maintenance" label="{{ __('keywords.for_maintenance') }}"
                        wire:model.blur="form.for_maintenance" />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-create-product')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="create">{{ __('keywords.add') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Edit Product Modal --}}
        <x-modal name="edit-product" title="{{ __('keywords.edit_product') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="form.name" label="{{ __('keywords.name') }}"
                        placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="form.name" required />
                    <x-input type="number" name="form.cost_price" label="{{ __('keywords.cost_price') }}"
                        placeholder="{{ __('keywords.enter_cost_price') }}" wire:model.blur="form.cost_price" required />
                    <x-input type="number" name="form.sell_price" label="{{ __('keywords.sell_price') }}"
                        placeholder="{{ __('keywords.enter_sell_price') }}" wire:model.blur="form.sell_price" required />
                    <x-input type="number" name="form.quantity" label="{{ __('keywords.quantity') }}"
                        placeholder="{{ __('keywords.enter_quantity') }}" wire:model.blur="form.quantity" required />
                    <x-input type="number" name="form.min_quantity" label="{{ __('keywords.min_quantity') }}"
                        placeholder="{{ __('keywords.enter_min_quantity') }}" wire:model.blur="form.min_quantity"
                        required />
                    <x-textarea name="form.description" label="{{ __('keywords.description') }}"
                        wire:model.blur="form.description" />
                    <x-select name="form.category_id" label="{{ __('keywords.category') }}"
                        wire:model.blur="form.category_id" :options="$this->categories->pluck('name', 'id')->toArray()"
                        placeholder="{{ __('keywords.select_category') }}" required />
                    <button type="button" wire:click="openCreateCategoryModal"
                        class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                        <i class="fas fa-tags me-1"></i>
                        {{ __('keywords.add_category') }}
                    </button>
                    <x-checkbox name="form.for_maintenance" label="{{ __('keywords.for_maintenance') }}"
                        wire:model.blur="form.for_maintenance" />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-edit-product')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="updateProduct">{{ __('keywords.update') }}</x-button>
            </x-slot:footer>
        </x-modal>

        <x-modal name="create-category-inline" title="{{ __('keywords.create_category') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-4">
                    <x-input name="newCategory.name" label="{{ __('keywords.name') }}"
                        placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="newCategory.name" required />
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary" @click="$dispatch('close-modal-create-category-inline')">
                    {{ __('keywords.cancel') }}
                </x-button>
                <x-button variant="primary"
                    wire:click="createCategoryInline">{{ __('keywords.add_category') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Delete Confirmation Modal --}}
        <x-confirm-modal name="delete-product" title="{{ __('keywords.delete_product') }}"
            message="{{ __('keywords.delete_product_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan

</div>
