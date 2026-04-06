<div>
    @if (session()->has('warning'))
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    <div class="flex h-[calc(100vh-7rem)] flex-col gap-6 lg:flex-row">
        <div class="flex min-w-0 flex-1 flex-col">
            <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    <button wire:click="setActiveCategory('all')"
                        class="shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ $activeCategory === 'all' ? 'bg-emerald-600 text-white' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                        {{ __('keywords.view_all') }}
                    </button>

                    @foreach ($categories as $category)
                        <button wire:click="setActiveCategory('{{ $category->id }}')"
                            class="shrink-0 rounded-lg px-4 py-2 text-sm font-medium transition-colors {{ (string) $activeCategory === (string) $category->id ? 'bg-emerald-600 text-white' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <div class="w-full sm:max-w-xs">
                    <x-input name="search" placeholder="{{ __('keywords.search') }}"
                        wire:model.live.debounce.300ms="search" />
                </div>
            </div>

            <div class="flex-1 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-4">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
                    @forelse ($this->products as $product)
                        <button wire:click="addToCart({{ $product->id }})"
                            class="group flex flex-col rounded-xl border border-gray-200 bg-white p-3 text-start shadow-sm transition-all hover:border-emerald-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            <span class="text-xs text-gray-500">
                                {{ $product->category?->name ?? __('keywords.not_specified') }}
                            </span>
                            <span class="mt-1 text-sm font-semibold text-gray-900 group-hover:text-emerald-600">
                                {{ $product->name }}
                            </span>
                            <span class="mt-3 text-xs text-gray-500">{{ __('keywords.cost_price') }}</span>
                            <span class="text-sm font-bold text-emerald-600">
                                {{ number_format($product->cost_price, 2) }} {{ __('keywords.currency') }}
                            </span>
                        </button>
                    @empty
                        <div
                            class="col-span-full rounded-lg border border-dashed border-gray-300 bg-white p-6 text-center text-sm text-gray-500">
                            {{ __('keywords.no_products_found') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="flex w-full flex-col rounded-xl border border-gray-200 bg-white shadow-sm lg:w-[420px]">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-semibold text-gray-900">{{ __('keywords.current_invoice') }}</h3>
                    @if ($this->cart_count > 0)
                        <span
                            class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-emerald-100 px-1.5 py-0.5 text-xs font-medium text-emerald-700">
                            {{ number_format($this->cart_count, 0) }}
                        </span>
                    @endif
                </div>

                @if (count($cart) > 0)
                    <button wire:click="clearCart" class="text-xs font-medium text-red-500 hover:text-red-700">
                        {{ __('keywords.delete') }}
                    </button>
                @endif
            </div>

            <div class="flex-1 overflow-y-auto p-4">
                @error('cart')
                    <p class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-600">
                        {{ $message }}</p>
                @enderror

                @if (count($cart) === 0)
                    <div class="flex h-full flex-col items-center justify-center py-8 text-center">
                        <p class="text-sm font-medium text-gray-500">{{ __('keywords.items_count') }}: 0</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($cart as $index => $item)
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3"
                                wire:key="cart-item-{{ $item['product_id'] }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $item['product_name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $item['category_name'] }}</p>
                                    </div>
                                    <button wire:click="removeFromCart({{ $index }})"
                                        class="shrink-0 p-1 text-gray-300 hover:text-red-500">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-1.5">
                                        <button wire:click="changeQuantity({{ $index }}, -1)"
                                            class="flex h-7 w-7 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-50">-</button>
                                        <span class="w-8 text-center text-sm font-medium text-gray-900">
                                            {{ rtrim(rtrim(number_format((float) $item['quantity'], 2), '0'), '.') }}
                                        </span>
                                        <button wire:click="changeQuantity({{ $index }}, 1)"
                                            class="flex h-7 w-7 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-50">+</button>
                                    </div>

                                    <div class="w-28">
                                        <label
                                            class="mb-1 block text-xs text-gray-500">{{ __('keywords.sell_price') }}</label>
                                        <input type="number" step="0.01" min="0.01"
                                            wire:model.live="cart.{{ $index }}.sell_price"
                                            class="w-full rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    </div>

                                    <div class="text-end">
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ number_format(((float) $item['sell_price']) * ((float) $item['quantity']), 2) }}
                                            {{ __('keywords.currency') }}
                                        </p>
                                    </div>
                                </div>

                                @error('cart.' . $index . '.sell_price')
                                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="border-t border-gray-200 bg-gray-50 p-5">
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between text-gray-500">
                        <span>{{ __('keywords.subtotal') }}</span>
                        <span>{{ number_format($this->base_total, 2) }} {{ __('keywords.currency') }}</span>
                    </div>
                    @if ($with_vat)
                        <div class="flex items-center justify-between text-gray-500">
                            <span>{{ __('keywords.vat_amount') }} (14%)</span>
                            <span>+ {{ number_format($this->vat_amount, 2) }} {{ __('keywords.currency') }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between text-gray-900">
                        <span>{{ __('keywords.invoice_total') }}</span>
                        <span class="font-semibold">{{ number_format($this->total_price, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    @if ($this->applied_customer_credit > 0)
                        <div class="flex items-center justify-between text-emerald-600">
                            <span>{{ __('keywords.applied_customer_credit') }}</span>
                            <span>- {{ number_format($this->applied_customer_credit, 2) }}
                                {{ __('keywords.currency') }}</span>
                        </div>
                    @endif
                    <div
                        class="flex items-center justify-between border-t border-gray-200 pt-2 text-base font-bold text-gray-900">
                        <span>{{ __('keywords.cash_due_now') }}</span>
                        <span>{{ number_format($this->cash_amount_due, 2) }} {{ __('keywords.currency') }}</span>
                    </div>
                </div>

                <div class="mt-4">
                    <x-button variant="primary" class="w-full" wire:click="openPaymentModal"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="openPaymentModal">{{ __('keywords.pay') }}</span>
                        <span wire:loading wire:target="openPaymentModal">{{ __('keywords.loading') }}</span>
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="sale-payment" title="{{ __('keywords.pay') }}" maxWidth="2xl">
        <x-slot:body>
            <div class="space-y-5">
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                    <div class="space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div x-data="{ open: false }" class="relative">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                {{ __('keywords.customer') }} <span class="text-red-500">*</span>
                            </label>

                            <input type="text" wire:model.live.debounce.300ms="customerSearch" @focus="open = true"
                                @click="open = true" @click.outside="open = false"
                                placeholder="{{ __('keywords.search_customer') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">

                            <input type="hidden" wire:model.live="customer_id">

                            <div x-show="open" x-cloak
                                class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                                <div class="max-h-52 overflow-y-auto">
                                    @forelse ($this->customers as $id => $name)
                                        <button type="button"
                                            wire:click="selectCustomer({{ $id }}, '{{ addslashes($name) }}')"
                                            @click="open = false"
                                            class="block w-full px-3 py-2 text-start text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 {{ (int) $customer_id === (int) $id ? 'bg-emerald-50 text-emerald-700' : '' }}">
                                            {{ $name }}
                                        </button>
                                    @empty
                                        <div class="px-3 py-2 text-sm text-gray-500">
                                            {{ __('keywords.no_customers_found') }}</div>
                                    @endforelse
                                </div>
                            </div>

                            @error('customer_id')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="button" wire:click="openCreateCustomerModal"
                            class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                            <i class="fas fa-user-plus me-1"></i>
                            {{ __('keywords.customer_not_found_add_new') }}
                        </button>

                        @if ($this->selectedCustomer)
                            <div
                                class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                                <div class="flex items-center justify-between gap-3">
                                    <span>{{ __('keywords.customer_balance') }}</span>
                                    <span
                                        class="font-semibold">{{ number_format($this->selectedCustomer->balance, 2) }}
                                        {{ __('keywords.currency') }}</span>
                                </div>
                                @if ($this->available_customer_credit > 0)
                                    <div class="mt-1 flex items-center justify-between gap-3">
                                        <span>{{ __('keywords.available_customer_credit') }}</span>
                                        <span
                                            class="font-semibold text-emerald-700">{{ number_format($this->available_customer_credit, 2) }}
                                            {{ __('keywords.currency') }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <x-input name="dealer_name" label="{{ __('keywords.dealer_name') }}"
                            placeholder="{{ __('keywords.dealer_name') }}" wire:model.live="dealer_name" />

                        @if ($canManageCreatedAt)
                            <x-input type="datetime-local" name="created_at" label="{{ __('keywords.created_at') }}"
                                wire:model.live="created_at" />
                        @endif
                    </div>

                    <div class="space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                {{ __('keywords.payment_type') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-2 flex gap-3">
                                <label
                                    class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2.5 transition-colors {{ $payment_type === 'cash' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                    <input type="radio" wire:model.live="payment_type" value="cash"
                                        class="text-emerald-600 focus:ring-emerald-500">
                                    {{ __('keywords.cash') }}
                                </label>
                                <label
                                    class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2.5 transition-colors {{ $payment_type === 'installment' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                    <input type="radio" wire:model.live="payment_type" value="installment"
                                        class="text-blue-600 focus:ring-blue-500">
                                    {{ __('keywords.installment') }}
                                </label>
                            </div>
                            @error('payment_type')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($payment_type === 'installment')
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <x-input name="down_payment" label="{{ __('keywords.down_payment') }}"
                                    placeholder="0.00" wire:model.live="down_payment" type="number" step="0.01"
                                    required />

                                <x-input name="installment_months" label="{{ __('keywords.installment_months') }}"
                                    placeholder="{{ __('keywords.enter_months_count') }}"
                                    wire:model.live="installment_months" type="number" min="1"
                                    max="60" required />

                                <x-input name="interest_rate" label="{{ __('keywords.interest_rate') }}"
                                    placeholder="0" wire:model.live="interest_rate" type="number" step="0.01"
                                    min="0" max="100" required />
                            </div>
                        @endif

                        <x-input name="discount" label="{{ __('keywords.discount') }}" placeholder="0.00"
                            wire:model.live="discount" type="number" step="0.01" min="0" />

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label
                                class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700">
                                <input type="checkbox" wire:model.live="with_vat"
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span>{{ __('keywords.apply_vat') }}</span>
                            </label>

                            <label
                                class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700">
                                <input type="checkbox" wire:model.live="includeWaterReading"
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span>{{ __('keywords.include_water_reading') }}</span>
                            </label>

                            <label
                                class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 sm:col-span-2">
                                <input type="checkbox" wire:model.live="printAfterSave"
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span>{{ __('keywords.print_after_save') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                @if ($includeWaterReading)
                    <div class="space-y-4 rounded-lg border border-emerald-100 bg-emerald-50/40 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm font-medium text-gray-700">
                                {{ __('keywords.filter') }} <span class="text-red-500">*</span>
                            </p>

                            <x-button variant="secondary" size="sm" wire:click="openCreateFilterModal"
                                wire:loading.attr="disabled" :disabled="!$customer_id">
                                <span wire:loading.remove wire:target="openCreateFilterModal">
                                    <i class="fas fa-plus text-xs"></i>
                                    {{ __('keywords.add_filter') }}
                                </span>
                                <span wire:loading
                                    wire:target="openCreateFilterModal">{{ __('keywords.loading') }}</span>
                            </x-button>
                        </div>

                        <div x-data="{
                            open: false,
                            search: @entangle('filterSearch'),
                            selected: @entangle('water_filter_id'),
                            filters: @js($customerFilters),
                            get filtered() {
                                const query = (this.search || '').toString().toLowerCase().trim();
                                if (!query) {
                                    return this.filters;
                                }
                                return this.filters.filter(f => f.label.toLowerCase().includes(query));
                            },
                            select(filter) {
                                $wire.selectFilter(filter.id, filter.label);
                                this.open = false;
                            },
                        }" class="relative">
                            <input type="text" x-model="search" @focus="open = true" @click="open = true"
                                @click.outside="open = false" placeholder="{{ __('keywords.select_filter') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-3 pe-8 text-sm text-gray-900 placeholder-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                {{ !$customer_id ? 'disabled' : '' }} />
                            <input type="hidden" wire:model="water_filter_id" />

                            @error('water_filter_id')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            @if (!$customer_id)
                                <p class="mt-1 text-xs text-gray-500">{{ __('keywords.select_customer_first') }}</p>
                            @elseif (empty($customerFilters))
                                <p class="mt-1 text-xs text-amber-600">{{ __('keywords.no_filters_for_customer') }}
                                </p>
                            @endif

                            <div x-show="open && filters.length > 0" x-cloak
                                class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg">
                                <div class="max-h-52 overflow-y-auto">
                                    <template x-for="filter in filtered" :key="filter.id">
                                        <button type="button" @click="select(filter)"
                                            class="block w-full px-3 py-2 text-start text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-700">
                                            <span x-text="filter.label"></span>
                                        </button>
                                    </template>
                                    <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500">
                                        {{ __('keywords.no_filters_found') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 border-t border-emerald-200 pt-3 sm:grid-cols-2">
                            <x-input name="waterReading.technician_name" label="{{ __('keywords.technician_name') }}"
                                placeholder="{{ __('keywords.technician_name') }}"
                                wire:model.live="waterReading.technician_name" required />

                            <x-input name="waterReading.tds" label="{{ __('keywords.tds') }}" placeholder="0"
                                wire:model.live="waterReading.tds" type="number" step="0.01" min="0"
                                required />

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                    {{ __('keywords.water_quality') }} <span class="text-red-500">*</span>
                                </label>
                                <select wire:model.live="waterReading.water_quality"
                                    class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    <option value="">{{ __('keywords.select_water_quality') }}</option>
                                    @foreach ($waterQualityOptions as $quality)
                                        <option value="{{ $quality->value }}">
                                            {{ __('keywords.' . $quality->label()) }}</option>
                                    @endforeach
                                </select>
                                @error('waterReading.water_quality')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex items-end pb-1">
                                <label
                                    class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700">
                                    <input type="checkbox" wire:model.live="waterReading.before_installment"
                                        class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span>{{ __('keywords.before_installment') }}</span>
                                </label>
                            </div>

                            @if ($waterReading['before_installment'] ?? false)
                                <div class="sm:col-span-2 space-y-3 rounded-lg border border-emerald-200 bg-white p-3">
                                    <label
                                        class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700">
                                        <input type="checkbox" wire:model.live="includeAfterInstallationReading"
                                            class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                        <span>{{ __('keywords.add_after_installment_reading') }}</span>
                                    </label>

                                    @if ($includeAfterInstallationReading)
                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                            <x-input name="afterWaterReading.technician_name"
                                                label="{{ __('keywords.technician_name') }}"
                                                placeholder="{{ __('keywords.technician_name') }}"
                                                wire:model.live="afterWaterReading.technician_name" required />

                                            <x-input name="afterWaterReading.tds" label="{{ __('keywords.tds') }}"
                                                placeholder="0" wire:model.live="afterWaterReading.tds"
                                                type="number" step="0.01" min="0" required />

                                            <div>
                                                <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                                    {{ __('keywords.water_quality') }} <span
                                                        class="text-red-500">*</span>
                                                </label>
                                                <select wire:model.live="afterWaterReading.water_quality"
                                                    class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                                    <option value="">{{ __('keywords.select_water_quality') }}
                                                    </option>
                                                    @foreach ($waterQualityOptions as $quality)
                                                        <option value="{{ $quality->value }}">
                                                            {{ __('keywords.' . $quality->label()) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('afterWaterReading.water_quality')
                                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="space-y-2 rounded-lg bg-gray-50 p-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('keywords.subtotal') }}</span>
                        <span class="font-medium">{{ number_format($this->base_total, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('keywords.discount') }}</span>
                        <span class="font-medium text-red-600">- {{ number_format($this->discount_amount, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('keywords.total_after_discount') }}</span>
                        <span class="font-medium">{{ number_format($this->total_after_discount, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    @if ($with_vat)
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ __('keywords.vat_amount') }} (14%)</span>
                            <span class="font-medium">+ {{ number_format($this->vat_amount, 2) }}
                                {{ __('keywords.currency') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('keywords.total_after_vat') }}</span>
                        <span class="font-medium">{{ number_format($this->subtotal_after_vat, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    @if ($this->applied_customer_credit > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ __('keywords.applied_customer_credit') }}</span>
                            <span class="font-medium text-emerald-600">-
                                {{ number_format($this->applied_customer_credit, 2) }}
                                {{ __('keywords.currency') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ __('keywords.cash_due_now') }}</span>
                        <span class="font-medium text-emerald-600">{{ number_format($this->cash_amount_due, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    @if ($payment_type === 'installment')
                        <div class="flex justify-between">
                            <span class="text-gray-500">{{ __('keywords.remaining_for_installments') }}</span>
                            <span
                                class="font-medium text-red-600">{{ number_format($this->remaining_after_down_payment, 2) }}
                                {{ __('keywords.currency') }}</span>
                        </div>
                        @if ((float) ($interest_rate ?: 0) > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-500">{{ __('keywords.interest_amount') }}</span>
                                <span class="font-medium text-amber-600">+
                                    {{ number_format($this->interest_amount, 2) }}
                                    {{ __('keywords.currency') }}</span>
                            </div>
                        @endif
                        @if ($this->installment_months_surcharge_total > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-500">{{ __('keywords.installment_monthly_fee') }}</span>
                                <span class="font-medium text-amber-600">+
                                    {{ number_format($this->installment_months_surcharge_total, 2) }}
                                    {{ __('keywords.currency') }}</span>
                            </div>
                            <p class="text-xs text-gray-500">{{ __('keywords.installment_monthly_fee_hint') }}</p>
                        @endif
                        <div class="flex justify-between border-t pt-2">
                            <span
                                class="font-medium text-gray-700">{{ __('keywords.installment_financed_total') }}</span>
                            <span class="font-bold text-gray-900">{{ number_format($this->installment_total, 2) }}
                                {{ __('keywords.currency') }}</span>
                        </div>
                        @if ((int) ($installment_months ?: 0) > 0)
                            <div class="flex justify-between border-t pt-2">
                                <span
                                    class="font-medium text-gray-700">{{ __('keywords.monthly_installment') }}</span>
                                <span
                                    class="font-bold text-blue-600">{{ number_format($this->installment_amount, 2) }}
                                    {{ __('keywords.currency') }} / {{ __('keywords.month') }}</span>
                            </div>
                        @endif
                    @endif

                    <div class="flex justify-between border-t pt-2">
                        <span class="font-medium text-gray-700">{{ __('keywords.invoice_total') }}</span>
                        <span class="font-bold text-gray-900">{{ number_format($this->total_price, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                </div>
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary" @click="$dispatch('close-modal-sale-payment')">
                {{ __('keywords.cancel') }}
            </x-button>
            <x-button variant="primary" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('keywords.save_sale') }}</span>
                <span wire:loading wire:target="save">{{ __('keywords.loading') }}</span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal name="create-filter-inline" title="{{ __('keywords.create_filter') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-4">
                <x-input name="newFilter.filter_model" label="{{ __('keywords.filter_model') }}"
                    placeholder="{{ __('keywords.enter_filter_model') }}" wire:model.blur="newFilter.filter_model"
                    required />

                <x-input name="newFilter.address" label="{{ __('keywords.filter_address') }}"
                    placeholder="{{ __('keywords.enter_address') }}" wire:model.blur="newFilter.address" required />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary" @click="$dispatch('close-modal-create-filter-inline')">
                {{ __('keywords.cancel') }}
            </x-button>
            <x-button variant="primary" wire:click="createFilterInline" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="createFilterInline">{{ __('keywords.add_filter') }}</span>
                <span wire:loading wire:target="createFilterInline">{{ __('keywords.loading') }}</span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal name="create-customer-inline" title="{{ __('keywords.add_customer') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-4">
                <x-input name="newCustomer.name" label="{{ __('keywords.name') }}"
                    placeholder="{{ __('keywords.enter_name') }}" wire:model.blur="newCustomer.name" required />

                <x-select name="newCustomer.place_id" label="{{ __('keywords.places') }}"
                    wire:model.blur="newCustomer.place_id" :options="$this->placeOptions"
                    placeholder="{{ __('keywords.select_place') }}" required />

                <button type="button" wire:click="openCreatePlaceModal"
                    class="text-xs font-medium text-emerald-600 hover:text-emerald-700">
                    <i class="fas fa-location-dot me-1"></i>
                    {{ __('keywords.add_place') }}
                </button>

                <x-input name="newCustomer.code" label="{{ __('keywords.code') }}"
                    placeholder="{{ __('keywords.enter_your_code') }}" wire:model.blur="newCustomer.code" />

                <x-phone-repeater name="newCustomer.phones" :label="__('keywords.phone')" :placeholder="__('keywords.enter_your_phone')" />

                <x-input name="newCustomer.national_number" label="{{ __('keywords.national_number') }}"
                    placeholder="{{ __('keywords.enter_national_number') }}"
                    wire:model.blur="newCustomer.national_number" />

                <x-input name="newCustomer.address" label="{{ __('keywords.address') }}"
                    placeholder="{{ __('keywords.enter_address') }}" wire:model.blur="newCustomer.address" />
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary" @click="$dispatch('close-modal-create-customer-inline')">
                {{ __('keywords.cancel') }}
            </x-button>
            <x-button variant="primary" wire:click="createCustomerInline">
                {{ __('keywords.add_customer') }}
            </x-button>
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
</div>
