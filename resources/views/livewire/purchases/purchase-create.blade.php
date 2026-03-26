<div>
    <x-page-header :title="__('keywords.create_purchase')" :description="__('keywords.create_purchase_description')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('purchases') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_purchases') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="space-y-6">
        {{-- Supplier & Payment Settings --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-900">
                    <i class="fas fa-file-invoice me-2 text-emerald-500"></i>
                    {{ __('keywords.purchase_info') }}
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <x-select name="supplier_id" label="{{ __('keywords.supplier') }}"
                            :options="$this->suppliers" wire:model.live="supplier_id"
                            :placeholder="__('keywords.select_supplier')" required />
                        <button type="button" wire:click="openCreateSupplierModal"
                            class="mt-1.5 text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                            <i class="fas fa-truck-fast me-1"></i>
                            {{ __('keywords.supplier_not_found_add_new') }}
                        </button>

                        @if($this->selectedSupplier)
                            <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                                <div class="flex items-center justify-between gap-3">
                                    <span>{{ __('keywords.supplier_balance') }}</span>
                                    <span class="font-semibold">{{ number_format($this->selectedSupplier->balance, 2) }} {{ __('keywords.currency') }}</span>
                                </div>
                                @if($this->available_supplier_credit > 0)
                                    <div class="mt-1 flex items-center justify-between gap-3">
                                        <span>{{ __('keywords.available_supplier_credit') }}</span>
                                        <span class="font-semibold text-emerald-700">{{ number_format($this->available_supplier_credit, 2) }} {{ __('keywords.currency') }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            {{ __('keywords.payment_type') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-3 mt-2">
                            <label class="flex items-center gap-2 cursor-pointer rounded-lg border px-4 py-2.5 transition-colors {{ $payment_type === 'cash' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                <input type="radio" wire:model.live="payment_type" value="cash" class="text-emerald-600 focus:ring-emerald-500">
                                <i class="fas fa-money-bill-wave text-sm"></i>
                                {{ __('keywords.cash') }}
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer rounded-lg border px-4 py-2.5 transition-colors {{ $payment_type === 'installment' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                <input type="radio" wire:model.live="payment_type" value="installment" class="text-blue-600 focus:ring-blue-500">
                                <i class="fas fa-calendar-check text-sm"></i>
                                {{ __('keywords.installment') }}
                            </label>
                        </div>
                        @error('payment_type')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($payment_type === 'installment')
                        <x-input name="down_payment" label="{{ __('keywords.down_payment') }}"
                            placeholder="0.00" wire:model.live="down_payment" type="number" step="0.01" required />

                        <x-input name="installment_months" label="{{ __('keywords.installment_months') }}"
                            placeholder="{{ __('keywords.enter_months_count') }}" wire:model.live="installment_months"
                            type="number" min="1" max="60" required />
                    @endif
                </div>
            </div>
        </div>

        {{-- Purchase Items --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">
                    <i class="fas fa-boxes-stacked me-2 text-amber-500"></i>
                    {{ __('keywords.purchase_items') }}
                </h3>
                <div class="flex items-center gap-2">
                    <x-button variant="secondary" size="sm" wire:click="openCreateProductModal()">
                        <i class="fas fa-box-open text-xs"></i>
                        {{ __('keywords.add_new_product') }}
                    </x-button>
                    <x-button variant="secondary" size="sm" wire:click="addItem">
                        <i class="fas fa-plus text-xs"></i>
                        {{ __('keywords.add_item') }}
                    </x-button>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @foreach($items as $index => $item)
                    <div class="flex flex-col sm:flex-row gap-3 items-start p-4 rounded-lg border border-gray-100 bg-gray-50/50 relative" wire:key="item-{{ $index }}">
                        <div class="flex-1 min-w-0">
                            <x-select name="items.{{ $index }}.product_id"
                                label="{{ __('keywords.product') }}"
                                :options="$this->products"
                                wire:model.live="items.{{ $index }}.product_id"
                                :placeholder="__('keywords.select_product')" required />
                            <button type="button" wire:click="openCreateProductModal({{ $index }})"
                                class="mt-1.5 text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                                <i class="fas fa-plus-circle me-1"></i>
                                {{ __('keywords.product_not_found_add_new') }}
                            </button>
                        </div>
                        <div class="w-full sm:w-36">
                            <x-input name="items.{{ $index }}.cost_price"
                                label="{{ __('keywords.cost_price') }}"
                                placeholder="0.00"
                                wire:model.live="items.{{ $index }}.cost_price"
                                type="number" step="0.01" required />
                        </div>
                        <div class="w-full sm:w-28">
                            <x-input name="items.{{ $index }}.quantity"
                                label="{{ __('keywords.quantity') }}"
                                placeholder="1"
                                wire:model.live="items.{{ $index }}.quantity"
                                type="number" step="1" min="1" required />
                        </div>
                        <div class="w-full sm:w-32 pt-0 sm:pt-7">
                            <div class="text-sm font-medium text-gray-700">
                                {{ number_format(((float)($item['cost_price'] ?: 0)) * ((float)($item['quantity'] ?: 0)), 2) }}
                                {{ __('keywords.currency') }}
                            </div>
                        </div>
                        @if(count($items) > 1)
                            <button wire:click="removeItem({{ $index }})"
                                class="absolute top-2 inset-e-2 sm:relative sm:top-auto sm:end-auto sm:mt-7 rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Summary & Submit --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-2 w-full sm:w-auto">
                        <div class="flex justify-between sm:gap-8 text-sm">
                            <span class="text-gray-500">{{ __('keywords.total_price') }}</span>
                            <span class="font-bold text-lg text-gray-900">
                                {{ number_format($this->total_price, 2) }} {{ __('keywords.currency') }}
                            </span>
                        </div>
                        @if($this->applied_supplier_credit > 0)
                            <div class="flex justify-between sm:gap-8 text-sm">
                                <span class="text-gray-500">{{ __('keywords.applied_supplier_credit') }}</span>
                                <span class="font-medium text-emerald-600">
                                    - {{ number_format($this->applied_supplier_credit, 2) }} {{ __('keywords.currency') }}
                                </span>
                            </div>
                        @endif
                        <div class="flex justify-between sm:gap-8 text-sm">
                            <span class="text-gray-500">{{ __('keywords.cash_due_now') }}</span>
                            <span class="font-medium text-emerald-600">
                                {{ number_format($this->cash_amount_due, 2) }} {{ __('keywords.currency') }}
                            </span>
                        </div>
                        @if($payment_type === 'installment')
                            <div class="flex justify-between sm:gap-8 text-sm">
                                <span class="text-gray-500">{{ __('keywords.down_payment') }}</span>
                                <span class="font-medium text-emerald-600">
                                    {{ number_format($this->cash_amount_due, 2) }} {{ __('keywords.currency') }}
                                </span>
                            </div>
                            <div class="flex justify-between sm:gap-8 text-sm">
                                <span class="text-gray-500">{{ __('keywords.remaining_for_installments') }}</span>
                                <span class="font-medium text-red-600">
                                    {{ number_format($this->remaining_after_down_payment, 2) }} {{ __('keywords.currency') }}
                                </span>
                            </div>
                            @if((int)($installment_months ?: 0) > 0)
                                <div class="flex justify-between sm:gap-8 text-sm border-t pt-2">
                                    <span class="text-gray-700 font-medium">{{ __('keywords.monthly_installment') }}</span>
                                    <span class="font-bold text-blue-600">
                                        {{ number_format($this->installment_amount, 2) }} {{ __('keywords.currency') }} / {{ __('keywords.month') }}
                                    </span>
                                </div>
                            @endif
                        @endif
                    </div>
                    <x-button variant="primary" size="lg" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-check me-1"></i>
                            {{ __('keywords.save_purchase') }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin me-1"></i>
                            {{ __('keywords.loading') }}
                        </span>
                    </x-button>
                </div>

                <div class="mt-4">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                        <input type="checkbox" wire:model.live="printAfterSave"
                            class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span>{{ __('keywords.print_after_save') }}</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="create-product-inline" title="{{ __('keywords.add_new_product') }}" maxWidth="2xl">
        <x-slot:body>
            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input name="newProduct.name" label="{{ __('keywords.name') }}"
                        placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="newProduct.name" required />

                    <x-input name="newProduct.cost_price" label="{{ __('keywords.cost_price') }}"
                        placeholder="0.00" type="number" step="0.01" wire:model.blur="newProduct.cost_price" required />

                    <x-input name="newProduct.min_quantity" label="{{ __('keywords.min_quantity') }}"
                        placeholder="0" type="number" min="0" wire:model.blur="newProduct.min_quantity" required />

                    <div>
                        <x-select name="newProduct.category_id" label="{{ __('keywords.category') }}"
                            :options="$this->categories" wire:model="newProduct.category_id"
                            :placeholder="__('keywords.select_category')" required />
                        <button type="button" @click="$dispatch('open-modal-create-category-inline')"
                            class="mt-1.5 text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                            <i class="fas fa-folder-plus me-1"></i>
                            {{ __('keywords.add_new_category') }}
                        </button>
                    </div>
                </div>

                <x-textarea name="newProduct.description" label="{{ __('keywords.description') }}"
                    placeholder="{{ __('keywords.enter_description') }}" wire:model.blur="newProduct.description" />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary" @click="$dispatch('close-modal-create-product-inline')">{{ __('keywords.cancel') }}</x-button>
            <x-button variant="primary" wire:click="createProductInline">{{ __('keywords.add_product') }}</x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal name="create-supplier-inline" title="{{ __('keywords.add_new_supplier') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                <x-input name="newSupplier.name" label="{{ __('keywords.name') }}"
                    placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="newSupplier.name" required />

                <x-input name="newSupplier.phone" label="{{ __('keywords.phone') }}"
                    placeholder="{{ __('keywords.enter_your_phone') }}" wire:model.blur="newSupplier.phone" />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary" @click="$dispatch('close-modal-create-supplier-inline')">{{ __('keywords.cancel') }}</x-button>
            <x-button variant="primary" wire:click="createSupplierInline">{{ __('keywords.add_supplier') }}</x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal name="create-category-inline" title="{{ __('keywords.add_new_category') }}" maxWidth="lg">
        <x-slot:body>
            <x-input name="newCategory.name" label="{{ __('keywords.name') }}"
                placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="newCategory.name" required />
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary" @click="$dispatch('close-modal-create-category-inline')">{{ __('keywords.cancel') }}</x-button>
            <x-button variant="primary" wire:click="createCategoryInline">{{ __('keywords.add_category') }}</x-button>
        </x-slot:footer>
    </x-modal>
</div>
