<div>
    <x-page-header :title="__('keywords.overdue_installments')" :description="__('keywords.overdue_installments_description')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('sales') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_sales') }}
            </x-button>
            @if ($visibleCount > 0)
                <x-button variant="secondary" wire:click="toggleSelectAll">
                    <i class="fas fa-check-square text-xs"></i>
                    {{ $selectedCount === $visibleCount ? __('keywords.clear_selection') : __('keywords.select_all') }}
                </x-button>
            @endif
            @if ($selectedCount > 0)
                <x-button variant="warning" wire:click="clearSelection">
                    <i class="fas fa-times text-xs"></i>
                    {{ __('keywords.clear_selection') }} ({{ $selectedCount }})
                </x-button>
                <x-button variant="primary"
                    href="{{ route('overdue-installments.print', ['ids' => implode(',', $selectedSales)]) }}"
                    target="_blank">
                    <i class="fas fa-print text-xs"></i>
                    {{ __('keywords.print_selected') }} ({{ $selectedCount }})
                </x-button>
            @else
                <x-button variant="primary" href="{{ route('overdue-installments.print') }}" target="_blank">
                    <i class="fas fa-print text-xs"></i>
                    {{ __('keywords.print') }}
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Summary Stat Cards --}}
    @if ($overdueSales->isNotEmpty())
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-stat-card :label="__('keywords.overdue_count')" :value="$overdueSales->total()" icon-class="fas fa-clock" color="red" />
            <x-stat-card :label="__('keywords.total_overdue_installments')" :value="number_format(collect($overdueSales->items())->sum('installment_amount'), 2)" :suffix="__('keywords.currency')" icon-class="fas fa-money-bill-wave"
                color="amber" />
            <x-stat-card :label="__('keywords.total_remaining_all')" :value="number_format(collect($overdueSales->items())->sum('remaining_amount'), 2)" :suffix="__('keywords.currency')" icon-class="fas fa-wallet"
                color="rose" />
        </div>
    @endif

    {{-- Search Toolbar --}}
    <x-search-toolbar :searchPlaceholder="__('keywords.search_by_customer_or_invoice')" searchModel="search" perPageModel="perPage" :perPageOptions="[10, 25, 50, 100]" />

    {{-- Data Table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'select', 'label' => __('keywords.select_all')],
        ['key' => 'number', 'label' => __('keywords.number')],
        ['key' => 'customer', 'label' => __('keywords.customer')],
        ['key' => 'phone', 'label' => __('keywords.phone')],
        ['key' => 'place', 'label' => __('keywords.place')],
        ['key' => 'installment', 'label' => __('keywords.overdue_installment_amount')],
        ['key' => 'remaining', 'label' => __('keywords.remaining_amount')],
        ['key' => 'next_date', 'label' => __('keywords.next_installment')],
        ['key' => 'overdue_days', 'label' => __('keywords.overdue_since')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($overdueSales as $sale)
            @php
                $customer = $sale->customer;
                $phones = $customer?->phone_numbers ?? [];
                $daysPastDue = $sale->next_installment_date ? (int) $sale->next_installment_date->diffInDays(now()) : 0;
                $checkboxId = 'overdue-sale-select-' . $sale->id;
            @endphp

            <tr wire:key="overdue-sale-row-{{ $sale->id }}" class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <label for="{{ $checkboxId }}" class="inline-flex items-center gap-2 cursor-pointer">
                        <span
                            class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-100 px-1 text-[10px] font-semibold text-gray-600">
                            {{ $sale->number }}
                        </span>
                        <input id="{{ $checkboxId }}" type="checkbox" value="{{ $sale->id }}"
                            wire:model.live="selectedSales" wire:key="{{ $checkboxId }}"
                            class="rounded border-gray-300" />
                    </label>
                </td>

                {{-- Invoice Number --}}
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $sale->number }}</span>
                </td>

                {{-- Customer --}}
                <td class="whitespace-nowrap px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $customer?->name ?? '—' }}</p>
                        @if ($customer?->code)
                            <p class="text-xs text-gray-400">{{ $customer->code }}</p>
                        @endif
                    </div>
                </td>

                {{-- Phone --}}
                <td class="whitespace-nowrap px-4 py-3">
                    @if (!empty($phones))
                        <span class="text-sm text-gray-700 font-medium">{{ implode(' - ', $phones) }}</span>
                    @else
                        <span class="text-sm text-gray-400 italic">{{ __('keywords.not_specified') }}</span>
                    @endif
                </td>

                {{-- Place --}}
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-600">{{ $customer?->place?->name ?? '—' }}</span>
                </td>

                {{-- Installment Amount (overdue) --}}
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-bold text-red-600">
                        {{ number_format($sale->installment_amount, 2) }} {{ __('keywords.currency') }}
                    </span>
                </td>

                {{-- Remaining --}}
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-rose-600">
                        {{ number_format($sale->remaining_amount, 2) }} {{ __('keywords.currency') }}
                    </span>
                </td>

                {{-- Next Installment Date --}}
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-red-600">
                        {{ $sale->next_installment_date?->format('Y/m/d') ?? '—' }}
                    </span>
                </td>

                {{-- Days Past Due --}}
                <td class="whitespace-nowrap px-4 py-3">
                    <x-badge :label="__('keywords.overdue_days', ['days' => $daysPastDue])" color="red" />
                </td>

                {{-- Actions --}}
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <div class="flex items-center justify-end gap-1">

                        @canany(['manage_sales', 'pay_sales'])
                            <button wire:click="openPayModal({{ $sale->id }})"
                                class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                title="{{ __('keywords.pay_installment') }}">
                                <img src="/images/icons/pay.svg" alt="" class="h-4 w-4" aria-hidden="true">
                                <span class="text-xs font-medium">{{ __('keywords.pay') }}</span>
                            </button>
                        @endcanany

                        @if ($customer)
                            <a href="{{ route('customers.view', $customer) }}"
                                class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-sky-50 hover:text-sky-600 transition-colors"
                                title="{{ __('keywords.view_customer') }}">
                                <i class="fas fa-user text-xs"></i>
                                <span class="text-xs font-medium">{{ __('keywords.customer') }}</span>
                            </a>
                        @endif

                        @canany(['manage_sales', 'view_sales'])
                            <a href="{{ route('sales.show', $sale) }}"
                                class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-sky-50 hover:text-sky-600 transition-colors"
                                title="{{ __('keywords.view_invoice') }}">
                                <img src="/images/icons/view.svg" alt="" class="h-4 w-4" aria-hidden="true">
                                <span class="text-xs font-medium">{{ __('keywords.view') }}</span>
                            </a>
                        @endcanany

                    </div>
                </td>
            </tr>

            {{-- Installment sub-row for extra context --}}
            <tr class="bg-amber-50/40">
                <td colspan="9" class="px-4 py-2">
                    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-600">
                        <span>
                            <i class="fas fa-home me-1 text-gray-400"></i>
                            {{ __('keywords.address') }}:
                            <strong>{{ $customer?->address ?: __('keywords.not_specified') }}</strong>
                        </span>
                        <span>
                            <i class="fas fa-coins me-1 text-amber-500"></i>
                            {{ __('keywords.installment_value') }}:
                            <strong>{{ number_format($sale->installment_amount, 2) }}
                                {{ __('keywords.currency') }}</strong>
                        </span>
                        <span>
                            <i class="fas fa-hashtag me-1 text-gray-400"></i>
                            {{ __('keywords.paid_installments') }}:
                            <strong>{{ $sale->paid_installments_count }} / {{ $sale->installment_months }}</strong>
                        </span>
                        <span>
                            <i class="fas fa-calendar-alt me-1 text-blue-400"></i>
                            {{ __('keywords.installment_start_date') }}:
                            <strong>{{ $sale->installment_start_date?->format('Y/m/d') ?? '—' }}</strong>
                        </span>
                    </div>
                </td>
            </tr>

        @empty
            <x-empty-state :title="__('keywords.no_overdue_installments')" :colspan="10" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$overdueSales" />

    {{-- Pay Modal --}}
    <x-modal name="pay-sale" title="{{ __('keywords.pay_installment') }}" maxWidth="lg">
        <x-slot:body>
            <div class="space-y-5">
                @if ($paySaleId)
                    @php
                        $payingSale = $overdueSales->firstWhere('id', $paySaleId);
                    @endphp
                    @if ($payingSale ?? null)
                        <div class="rounded-lg bg-gray-50 p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">{{ __('keywords.customer') }}</span>
                                <span class="font-medium">{{ $payingSale->customer?->name }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">{{ __('keywords.total_price') }}</span>
                                <span class="font-medium">{{ number_format($payingSale->total_price, 2) }}
                                    {{ __('keywords.currency') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">{{ __('keywords.paid_amount') }}</span>
                                <span
                                    class="font-medium text-emerald-600">{{ number_format($payingSale->paid_amount, 2) }}
                                    {{ __('keywords.currency') }}</span>
                            </div>
                            <div class="flex justify-between text-sm border-t pt-2">
                                <span class="text-gray-700 font-medium">{{ __('keywords.remaining_amount') }}</span>
                                <span
                                    class="font-bold text-red-600">{{ number_format($payingSale->remaining_amount, 2) }}
                                    {{ __('keywords.currency') }}</span>
                            </div>
                            <div class="flex justify-between text-sm border-t pt-2">
                                <span class="text-gray-700 font-medium">{{ __('keywords.installment_value') }}</span>
                                <span
                                    class="font-bold text-blue-600">{{ number_format($payingSale->installment_amount, 2) }}
                                    {{ __('keywords.currency') }}</span>
                            </div>
                        </div>
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
            <x-button variant="secondary" @click="$dispatch('close-modal-pay-sale')">
                {{ __('keywords.cancel') }}
            </x-button>
            <x-button variant="primary" wire:click="submitPayment">
                <i class="fas fa-check me-1"></i>
                {{ __('keywords.confirm_payment') }}
            </x-button>
        </x-slot:footer>
    </x-modal>

</div>
