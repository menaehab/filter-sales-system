<div>
    <x-page-header :title="__('keywords.create_purchase_return')" :description="__('keywords.create_purchase_return_description')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('purchase-returns') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_purchase_returns') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="space-y-6">
        {{-- Purchase Number Search --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-900">
                    <i class="fas fa-search me-2 text-emerald-500"></i>
                    {{ __('keywords.search_purchase') }}
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <x-input name="purchase_number" label="{{ __('keywords.purchase_number') }}"
                            placeholder="{{ __('keywords.enter_purchase_number') }}"
                            wire:model.live.debounce.500ms="purchase_number" required />
                    </div>

                    @if ($this->purchase)
                        <div class="sm:col-span-2">
                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 space-y-2">
                                <div class="flex items-center gap-2 text-sm font-medium text-emerald-800">
                                    <i class="fas fa-check-circle"></i>
                                    {{ __('keywords.purchase_found') }}
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-500">{{ __('keywords.supplier') }}:</span>
                                        <span class="font-medium text-gray-900">{{ $this->purchase->supplier_name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">{{ __('keywords.total_price') }}:</span>
                                        <span
                                            class="font-medium text-gray-900">{{ number_format($this->purchase->total_price, 2) }}
                                            {{ __('keywords.currency') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">{{ __('keywords.date') }}:</span>
                                        <span
                                            class="font-medium text-gray-900">{{ $this->purchase->created_at->format('Y-m-d') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">{{ __('keywords.purchase_items') }}:</span>
                                        <span class="font-medium text-gray-900">{{ $this->purchase->items->count() }}
                                            {{ __('keywords.items') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Purchase Items for Return --}}
        @if ($this->purchase && count($items) > 0)
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        <i class="fas fa-boxes-stacked me-2 text-amber-500"></i>
                        {{ __('keywords.select_return_items') }}
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">{{ __('keywords.select_return_items_description') }}</p>
                </div>
                <div class="p-6 space-y-3">
                    @error('items')
                        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                            <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                        </div>
                    @enderror

                    @foreach ($items as $index => $item)
                        <div wire:key="return-item-{{ $index }}"
                            class="flex flex-col sm:flex-row gap-4 items-start p-4 rounded-lg border transition-colors {{ $item['selected'] ? 'border-emerald-300 bg-emerald-50/50' : 'border-gray-100 bg-gray-50/50' }}">
                            {{-- Checkbox --}}
                            <div class="flex items-center pt-0 sm:pt-7">
                                <input type="checkbox" wire:model.live="items.{{ $index }}.selected"
                                    class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            </div>

                            {{-- Product Name --}}
                            <div class="flex-1 min-w-0">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('keywords.product') }}</label>
                                <div class="text-sm font-medium text-gray-900 py-2">{{ $item['product_name'] }}</div>
                            </div>

                            {{-- Cost Price --}}
                            <div class="w-full sm:w-36">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('keywords.cost_price') }}</label>
                                <div class="text-sm text-gray-700 py-2">
                                    {{ number_format((float) $item['cost_price'], 2) }} {{ __('keywords.currency') }}
                                </div>
                            </div>

                            {{-- Available Quantity --}}
                            <div class="w-full sm:w-28">
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('keywords.available_quantity') }}</label>
                                <div class="text-sm text-gray-700 py-2">
                                    {{ number_format($item['available_quantity'], 2) }}</div>
                            </div>

                            {{-- Return Quantity --}}
                            <div class="w-full sm:w-28">
                                <x-input name="items.{{ $index }}.return_quantity"
                                    label="{{ __('keywords.return_quantity') }}" placeholder="0"
                                    wire:model.live="items.{{ $index }}.return_quantity" type="number"
                                    min="1" step="1" :max="$item['available_quantity']" :disabled="!$item['selected']" />
                            </div>

                            {{-- Line Total --}}
                            <div class="w-full sm:w-32 pt-0 sm:pt-7">
                                <div
                                    class="text-sm font-medium {{ $item['selected'] ? 'text-red-600' : 'text-gray-400' }}">
                                    {{ number_format(((float) ($item['cost_price'] ?: 0)) * ((float) ($item['return_quantity'] ?: 0)), 2) }}
                                    {{ __('keywords.currency') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Return Details --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        <i class="fas fa-info-circle me-2 text-blue-500"></i>
                        {{ __('keywords.return_details') }}
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-textarea name="reason" label="{{ __('keywords.reason') }}" class="col-span-2"
                            placeholder="{{ __('keywords.enter_return_reason') }}" wire:model="reason" />

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700">
                                {{ __('keywords.cash_refund') }}
                            </label>
                            <div class="flex gap-3 mt-2">
                                <label
                                    class="flex items-center gap-2 cursor-pointer rounded-lg border px-4 py-2.5 transition-colors {{ $cash_refund ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                    <input type="radio" wire:model.live="cash_refund" value="1"
                                        class="text-emerald-600 focus:ring-emerald-500">
                                    <i class="fas fa-money-bill-wave text-sm"></i>
                                    {{ __('keywords.yes') }}
                                </label>
                                <label
                                    class="flex items-center gap-2 cursor-pointer rounded-lg border px-4 py-2.5 transition-colors {{ !$cash_refund ? 'border-red-500 bg-red-50 text-red-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                    <input type="radio" wire:model.live="cash_refund" value="0"
                                        class="text-red-600 focus:ring-red-500">
                                    <i class="fas fa-times-circle text-sm"></i>
                                    {{ __('keywords.no') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary & Submit --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-2 w-full sm:w-auto">
                            <div class="flex justify-between sm:gap-8 text-sm">
                                <span class="text-gray-500">{{ __('keywords.selected_items_count') }}</span>
                                <span class="font-bold text-lg text-gray-900">{{ $this->selected_items_count }}</span>
                            </div>
                            <div class="flex justify-between sm:gap-8 text-sm">
                                <span class="text-gray-500">{{ __('keywords.total_return_price') }}</span>
                                <span class="font-bold text-lg text-red-600">
                                    {{ number_format($this->total_return_price, 2) }} {{ __('keywords.currency') }}
                                </span>
                            </div>
                        </div>
                        <x-button variant="primary" size="lg" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-check me-1"></i>
                                {{ __('keywords.save_purchase_return') }}
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="fas fa-spinner fa-spin me-1"></i>
                                {{ __('keywords.loading') }}
                            </span>
                        </x-button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
