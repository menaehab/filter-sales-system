<div x-on:confirmed-delete-purchase.window="$wire.delete()">
    <x-page-header :title="__('keywords.purchases')" :description="__('keywords.purchases_management')">
        @canany(['manage_purchases', 'add_purchases'])
            <x-slot:actions>
                <x-button variant="primary" href="{{ route('purchases.create') }}">
                    <i class="fas fa-plus text-xs"></i>
                    {{ __('keywords.add_purchase') }}
                </x-button>
            </x-slot:actions>
        @endcanany
    </x-page-header>

    <x-search-toolbar>
        <select wire:model.live="filterPaymentType"
            class="rounded-lg border border-gray-300 bg-white py-2 ps-3 pe-8 text-sm text-gray-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <option value="">{{ __('keywords.all_payment_types') }}</option>
            <option value="cash">{{ __('keywords.cash') }}</option>
            <option value="installment">{{ __('keywords.installment') }}</option>
        </select>
        <select wire:model.live="filterStatus"
            class="rounded-lg border border-gray-300 bg-white py-2 ps-3 pe-8 text-sm text-gray-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            <option value="">{{ __('keywords.all_statuses') }}</option>
            <option value="paid">{{ __('keywords.fully_paid') }}</option>
            <option value="partial">{{ __('keywords.partially_paid') }}</option>
            <option value="unpaid">{{ __('keywords.unpaid') }}</option>
        </select>
        <x-input type="date" name="dateFrom" wire:model.live="dateFrom" class="w-full sm:w-auto"
            placeholder="{{ __('keywords.from_date') }}" />
        <x-input type="date" name="dateTo" wire:model.live="dateTo" class="w-full sm:w-auto"
            placeholder="{{ __('keywords.to_date') }}" />
    </x-search-toolbar>

    {{-- Purchases table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'number', 'label' => __('keywords.number')],
        ['key' => 'supplier', 'label' => __('keywords.supplier')],
        ['key' => 'total', 'label' => __('keywords.total_price')],
        ['key' => 'paid', 'label' => __('keywords.paid_amount')],
        ['key' => 'remaining', 'label' => __('keywords.remaining_amount')],
        ['key' => 'type', 'label' => __('keywords.payment_type')],
        ['key' => 'status', 'label' => __('keywords.status')],
        ['key' => 'date', 'label' => __('keywords.created_at')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->purchases as $purchase)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $purchase->number }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $purchase->supplier_name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 font-medium">
                    {{ number_format($purchase->total_price, 2) }} {{ __('keywords.currency') }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-emerald-600 font-medium">
                    {{ number_format($purchase->paid_amount, 2) }} {{ __('keywords.currency') }}
                </td>
                <td
                    class="whitespace-nowrap px-4 py-3 text-sm font-medium {{ $purchase->remaining_amount > 0 ? 'text-red-600' : 'text-gray-500' }}">
                    {{ number_format($purchase->remaining_amount, 2) }} {{ __('keywords.currency') }}
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    @if ($purchase->isInstallment())
                        <x-badge :label="__('keywords.installment')" color="blue" />
                    @else
                        <x-badge :label="__('keywords.cash')" color="green" />
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    @if ($purchase->isFullyPaid())
                        <x-badge :label="__('keywords.fully_paid')" color="green" />
                    @elseif($purchase->paid_amount > 0)
                        <x-badge :label="__('keywords.partially_paid')" color="yellow" />
                    @else
                        <x-badge :label="__('keywords.unpaid')" color="red" />
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                    {{ $purchase->created_at->format('Y-m-d') }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('purchases.print', $purchase) }}" target="_blank"
                            class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-purple-50 hover:text-purple-600 transition-colors"
                            title="{{ __('keywords.print') }}">
                            <img src="/images/icons/print.svg" alt="" class="h-4 w-4" aria-hidden="true">
                            <span class="text-xs font-medium">{{ __('keywords.print') }}</span>
                        </a>

                        @canany(['manage_purchases', 'pay_purchases'])
                            @if (!$purchase->isFullyPaid())
                                <button wire:click="openPayModal({{ $purchase->id }})"
                                    class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    title="{{ __('keywords.pay') }}">
                                    <img src="/images/icons/pay.svg" alt="" class="h-4 w-4" aria-hidden="true">
                                    <span class="text-xs font-medium">{{ __('keywords.pay') }}</span>
                                </button>
                            @endif
                        @endcanany

                        @canany(['manage_purchases', 'view_purchases'])
                            <a href="{{ route('purchases.show', $purchase) }}"
                                class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-sky-50 hover:text-sky-600 transition-colors"
                                title="{{ __('keywords.view') }}">
                                <img src="/images/icons/view.svg" alt="" class="h-4 w-4" aria-hidden="true">
                                <span class="text-xs font-medium">{{ __('keywords.view') }}</span>
                            </a>
                        @endcanany

                        @canany(['manage_purchases', 'edit_purchases'])
                            <a href="{{ route('purchases.edit', $purchase) }}"
                                class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-sky-50 hover:text-sky-600 transition-colors"
                                title="{{ __('keywords.edit') }}">
                                <img src="/images/icons/edit.svg" alt="" class="h-4 w-4" aria-hidden="true">
                                <span class="text-xs font-medium">{{ __('keywords.edit') }}</span>
                            </a>
                        @endcanany

                        @can('manage_purchases')
                            <button wire:click="setDelete({{ $purchase->id }})"
                                class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors"
                                title="{{ __('keywords.delete') }}">
                                <img src="/images/icons/delete.svg" alt="" class="h-4 w-4" aria-hidden="true">
                                <span class="text-xs font-medium">{{ __('keywords.delete') }}</span>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>

            {{-- Installment info row --}}
            @if ($purchase->isInstallment() && !$purchase->isFullyPaid())
                <tr class="bg-blue-50/50">
                    <td colspan="9" class="px-4 py-2">
                        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-600">
                            <span>
                                <i class="fas fa-calendar-alt me-1 text-blue-500"></i>
                                {{ __('keywords.next_installment') }}:
                                <strong
                                    class="{{ $purchase->next_installment_date && $purchase->next_installment_date->lte(now()) ? 'text-red-600' : 'text-gray-800' }}">
                                    {{ $purchase->next_installment_date?->format('Y-m-d') ?? '—' }}
                                </strong>
                            </span>
                            <span>
                                <i class="fas fa-coins me-1 text-amber-500"></i>
                                {{ __('keywords.installment_value') }}:
                                <strong>{{ number_format($purchase->installment_amount, 2) }}
                                    {{ __('keywords.currency') }}</strong>
                            </span>
                            <span>
                                <i class="fas fa-hashtag me-1 text-gray-400"></i>
                                {{ __('keywords.paid_installments') }}:
                                <strong>{{ $purchase->paid_installments_count }} /
                                    {{ $purchase->installment_months }}</strong>
                            </span>
                        </div>
                    </td>
                </tr>
            @endif
        @empty
            <x-empty-state :title="__('keywords.no_purchases_found')" :colspan="9" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->purchases" />

    {{-- Pay Modal --}}
    <x-modal name="pay-purchase" title="{{ __('keywords.pay_installment') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                @if ($payPurchaseId)
                    @php
                        $payingPurchase = $this->purchases->firstWhere('id', $payPurchaseId);
                    @endphp
                    @if ($payingPurchase)
                        <div class="rounded-lg bg-gray-50 p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">{{ __('keywords.total_price') }}</span>
                                <span class="font-medium">{{ number_format($payingPurchase->total_price, 2) }}
                                    {{ __('keywords.currency') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">{{ __('keywords.paid_amount') }}</span>
                                <span
                                    class="font-medium text-emerald-600">{{ number_format($payingPurchase->paid_amount, 2) }}
                                    {{ __('keywords.currency') }}</span>
                            </div>
                            <div class="flex justify-between text-sm border-t pt-2">
                                <span class="text-gray-700 font-medium">{{ __('keywords.remaining_amount') }}</span>
                                <span
                                    class="font-bold text-red-600">{{ number_format($payingPurchase->remaining_amount, 2) }}
                                    {{ __('keywords.currency') }}</span>
                            </div>
                            @if ($payingPurchase->isInstallment())
                                <div class="flex justify-between text-sm border-t pt-2">
                                    <span
                                        class="text-gray-700 font-medium">{{ __('keywords.installment_value') }}</span>
                                    <span
                                        class="font-bold text-blue-600">{{ number_format($payingPurchase->installment_amount, 2) }}
                                        {{ __('keywords.currency') }}</span>
                                </div>
                            @endif
                        </div>

                        @if ($payFromPurchaseId && $payFromPurchaseId !== $payingPurchase->id)
                            <div
                                class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                {{ __('keywords.payment_allocated_oldest_first') }}
                            </div>
                        @endif
                    @endif
                @endif

                <x-input name="payAmount" label="{{ __('keywords.amount') }}"
                    placeholder="{{ __('keywords.enter_amount') }}" wire:model="payAmount" type="number"
                    step="0.01" required />

                <x-select name="payMethod" label="{{ __('keywords.payment_method') }}" :options="['cash' => __('keywords.cash'), 'bank_transfer' => __('keywords.bank_transfer')]"
                    wire:model="payMethod" :placeholder="__('keywords.select_payment_method')" />

                <x-textarea name="payNote" label="{{ __('keywords.note') }}"
                    placeholder="{{ __('keywords.enter_note') }}" wire:model="payNote" />

                @if ($canManageCreatedAt)
                    <x-input type="datetime-local" name="payCreatedAt" label="{{ __('keywords.created_at') }}"
                        wire:model.live="payCreatedAt" />
                @endif

                <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                    <input type="checkbox" wire:model.live="printAfterPayment"
                        class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span>{{ __('keywords.print_after_payment') }}</span>
                </label>
            </div>
        </x-slot:body>
        <x-slot:footer>
            <x-button variant="secondary"
                @click="$dispatch('close-modal-pay-purchase')">{{ __('keywords.cancel') }}</x-button>
            <x-button variant="primary" wire:click="submitPayment">
                <i class="fas fa-check me-1"></i>
                {{ __('keywords.confirm_payment') }}
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-modal name="delete-purchase" title="{{ __('keywords.delete_purchase') }}"
        message="{{ __('keywords.delete_purchase_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
        variant="danger" />
</div>
