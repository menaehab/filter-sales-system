<div>
    <x-page-header :title="__('keywords.edit_sale')" :description="__('keywords.edit_sale_description')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('sales') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_sales') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-900">
                    <i class="fas fa-receipt me-2 text-emerald-500"></i>
                    {{ __('keywords.sale_info') }}
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <x-select name="customer_id" label="{{ __('keywords.customer') }}" :options="$customers"
                        wire:model.live="customer_id" :placeholder="__('keywords.select_customer')" required />

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            {{ __('keywords.payment_type') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-3 mt-2">
                            <label
                                class="flex items-center gap-2 cursor-pointer rounded-lg border px-4 py-2.5 transition-colors {{ $payment_type === 'cash' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                                <input type="radio" wire:model.live="payment_type" value="cash"
                                    class="text-emerald-600 focus:ring-emerald-500">
                                {{ __('keywords.cash') }}
                            </label>
                            <label
                                class="flex items-center gap-2 cursor-pointer rounded-lg border px-4 py-2.5 transition-colors {{ $payment_type === 'installment' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">
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
                        <x-input name="down_payment" label="{{ __('keywords.down_payment') }}" placeholder="0.00"
                            wire:model.live="down_payment" type="number" step="0.01" required />

                        <x-input name="installment_months" label="{{ __('keywords.installment_months') }}"
                            placeholder="{{ __('keywords.enter_months_count') }}" wire:model.live="installment_months"
                            type="number" min="1" max="60" required />

                        <x-input name="interest_rate" label="{{ __('keywords.interest_rate') }}" placeholder="0"
                            wire:model.live="interest_rate" type="number" step="0.01" min="0" max="100"
                            required />
                    @endif

                    <x-input name="discount" label="{{ __('keywords.discount') }}" placeholder="0.00"
                        wire:model.live="discount" type="number" step="0.01" min="0" />

                    <div class="flex items-center pt-2">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                            <input type="checkbox" wire:model.live="with_vat"
                                class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span>{{ __('keywords.apply_vat') }}</span>
                        </label>
                    </div>

                    <x-input name="dealer_name" label="{{ __('keywords.dealer_name') }}"
                        placeholder="{{ __('keywords.enter_dealer_name') }}" wire:model.live="dealer_name"
                        type="text" maxlength="255" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900">
                    <i class="fas fa-boxes-stacked me-2 text-amber-500"></i>
                    {{ __('keywords.sale_items') }}
                </h3>
                <x-button variant="secondary" size="sm" wire:click="addItem">
                    <i class="fas fa-plus text-xs"></i>
                    {{ __('keywords.add_item') }}
                </x-button>
            </div>
            <div class="p-6 space-y-4">
                @foreach ($items as $index => $item)
                    <div class="flex flex-col sm:flex-row gap-3 items-start p-4 rounded-lg border border-gray-100 bg-gray-50/50 relative"
                        wire:key="item-{{ $index }}">
                        <div class="flex-1 min-w-0">
                            <x-select name="items.{{ $index }}.product_id" label="{{ __('keywords.product') }}"
                                :options="$products" wire:model.live="items.{{ $index }}.product_id"
                                :placeholder="__('keywords.select_product')" required />
                        </div>
                        <div class="w-full sm:w-36">
                            <x-input name="items.{{ $index }}.sell_price"
                                label="{{ __('keywords.sell_price') }}" placeholder="0.00"
                                wire:model.live="items.{{ $index }}.sell_price" type="number" step="0.01"
                                required />
                        </div>
                        <div class="w-full sm:w-28">
                            <x-input name="items.{{ $index }}.quantity" label="{{ __('keywords.quantity') }}"
                                placeholder="1" wire:model.live="items.{{ $index }}.quantity" type="number"
                                step="1" min="1" required />
                        </div>
                        <div class="w-full sm:w-32 pt-0 sm:pt-7">
                            <div class="text-sm font-medium text-gray-700">
                                {{ number_format(((float) ($item['sell_price'] ?: 0)) * ((float) ($item['quantity'] ?: 0)), 2) }}
                                {{ __('keywords.currency') }}
                            </div>
                        </div>
                        @if (count($items) > 1)
                            <button wire:click="removeItem({{ $index }})"
                                class="absolute top-2 inset-e-2 sm:relative sm:top-auto sm:end-auto sm:mt-7 rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="space-y-2 w-full sm:w-auto min-w-[300px]">
                        <div class="flex justify-between sm:gap-8 text-sm">
                            <span class="text-gray-500">{{ __('keywords.subtotal') }}</span>
                            <span class="font-medium text-gray-900">
                                {{ number_format($this->base_total, 2) }} {{ __('keywords.currency') }}
                            </span>
                        </div>
                        <div class="flex justify-between sm:gap-8 text-sm">
                            <span class="text-gray-500">{{ __('keywords.discount') }}</span>
                            <span class="font-medium text-red-600">
                                - {{ number_format($this->discount_amount, 2) }} {{ __('keywords.currency') }}
                            </span>
                        </div>
                        <div class="flex justify-between sm:gap-8 text-sm">
                            <span class="text-gray-500">{{ __('keywords.total_after_discount') }}</span>
                            <span class="font-medium text-gray-900">
                                {{ number_format($this->total_after_discount, 2) }} {{ __('keywords.currency') }}
                            </span>
                        </div>
                        @if ($with_vat)
                            <div class="flex justify-between sm:gap-8 text-sm">
                                <span class="text-gray-500">{{ __('keywords.vat_amount') }} (14%)</span>
                                <span class="font-medium text-emerald-600">
                                    + {{ number_format($this->vat_amount, 2) }} {{ __('keywords.currency') }}
                                </span>
                            </div>
                        @endif

                        <div class="flex justify-between sm:gap-8 text-sm border-t border-gray-100 pt-2 mt-2">
                            <span class="text-gray-700 font-medium">{{ __('keywords.total_after_vat') }}</span>
                            <span class="font-bold text-gray-900">
                                {{ number_format($this->subtotal_after_vat, 2) }} {{ __('keywords.currency') }}
                            </span>
                        </div>

                        @if ($payment_type === 'installment')
                            <div class="flex justify-between sm:gap-8 text-sm">
                                <span class="text-gray-500">{{ __('keywords.down_payment') }}</span>
                                <span class="font-medium text-emerald-600">
                                    {{ number_format((float) ($down_payment ?: 0), 2) }} {{ __('keywords.currency') }}
                                </span>
                            </div>
                            <div class="flex justify-between sm:gap-8 text-sm">
                                <span class="text-gray-500">{{ __('keywords.remaining_for_installments') }}</span>
                                <span class="font-medium text-red-600">
                                    {{ number_format($this->remaining_after_down_payment, 2) }}
                                    {{ __('keywords.currency') }}
                                </span>
                            </div>
                            @if ((float) ($interest_rate ?: 0) > 0)
                                <div class="flex justify-between sm:gap-8 text-sm">
                                    <span class="text-gray-500">{{ __('keywords.interest_amount') }}</span>
                                    <span class="font-medium text-amber-600">
                                        + {{ number_format($this->interest_amount, 2) }} {{ __('keywords.currency') }}
                                    </span>
                                </div>
                            @endif
                            @if ($this->installment_months_surcharge_total > 0)
                                <div class="flex justify-between sm:gap-8 text-sm">
                                    <span class="text-gray-500">{{ __('keywords.installment_monthly_fee') }}</span>
                                    <span class="font-medium text-amber-600">
                                        + {{ number_format($this->installment_months_surcharge_total, 2) }}
                                        {{ __('keywords.currency') }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">{{ __('keywords.installment_monthly_fee_hint') }}</p>
                            @endif
                            <div class="flex justify-between sm:gap-8 text-sm border-t border-gray-100 pt-2 mt-2">
                                <span
                                    class="text-gray-700 font-medium">{{ __('keywords.installment_financed_total') }}</span>
                                <span class="font-bold text-gray-900">
                                    {{ number_format($this->installment_total, 2) }} {{ __('keywords.currency') }}
                                </span>
                            </div>
                            @if ((int) ($installment_months ?: 0) > 0)
                                <div class="flex justify-between sm:gap-8 text-sm border-t pt-2">
                                    <span
                                        class="text-gray-700 font-medium">{{ __('keywords.monthly_installment') }}</span>
                                    <span class="font-bold text-blue-600">
                                        {{ number_format($this->installment_amount, 2) }}
                                        {{ __('keywords.currency') }} / {{ __('keywords.month') }}
                                    </span>
                                </div>
                            @endif
                        @endif

                        <div
                            class="flex justify-between sm:gap-8 text-base border-t border-gray-200 pt-2 mt-4 font-bold text-gray-900">
                            <span>{{ __('keywords.invoice_total') }}</span>
                            <span>{{ number_format($this->total_price, 2) }} {{ __('keywords.currency') }}</span>
                        </div>
                    </div>
                    <x-button variant="primary" size="lg" wire:click="update" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="update">
                            <i class="fas fa-check me-1"></i>
                            {{ __('keywords.update_sale') }}
                        </span>
                        <span wire:loading wire:target="update">
                            <i class="fas fa-spinner fa-spin me-1"></i>
                            {{ __('keywords.loading') }}
                        </span>
                    </x-button>
                </div>
            </div>
        </div>

        @if ($sale->paymentAllocations->count() > 0)
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">
                        <i class="fas fa-history me-2 text-purple-500"></i>
                        {{ __('keywords.payment_history') }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    #</th>
                                <th
                                    class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    {{ __('keywords.amount') }}</th>
                                <th
                                    class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    {{ __('keywords.payment_method') }}</th>
                                <th
                                    class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    {{ __('keywords.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($sale->paymentAllocations as $i => $allocation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-emerald-600">
                                        {{ number_format($allocation->amount, 2) }} {{ __('keywords.currency') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ __('keywords.' . $allocation->customerPayment?->payment_method) ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ $allocation->created_at->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
