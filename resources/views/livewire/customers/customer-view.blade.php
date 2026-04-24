<div x-on:confirmed-delete-customer-view-payment.window="$wire.deletePayment()">
    <x-page-header :title="$customer->name" :description="__('keywords.customer_details_description')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('customers') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_customers') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.customer') }}</h3>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.name') }}</span>
                    <span class="font-medium text-gray-900">{{ $customer->name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.code') }}</span>
                    <span class="font-medium text-gray-900">{{ $customer->code }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.phone') }}</span>
                    <span class="font-medium text-gray-900">
                        {{ $customer->phone_numbers !== [] ? implode(' - ', $customer->phone_numbers) : __('keywords.not_specified') }}
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.place') }}</span>
                    <span
                        class="font-medium text-gray-900">{{ $customer->place?->name ?? __('keywords.not_specified') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.address') }}</span>
                    <span class="font-medium text-gray-900">{{ $customer->address }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.created_at') }}</span>
                    <span class="font-medium text-gray-900">{{ $customer->created_at?->format('Y/m/d H:i') }}</span>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.customer_balance') }}</h3>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.total_sales') }}</span>
                    <span class="font-bold text-gray-900">{{ number_format($customer->total_sales, 2) }}
                        {{ __('keywords.currency') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.total_payments') }}</span>
                    <span class="font-medium text-emerald-600">{{ number_format($customer->total_payments, 2) }}
                        {{ __('keywords.currency') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.total_returns_without_cash') }}</span>
                    <span
                        class="font-medium text-amber-600">{{ number_format($customer->total_returns_without_cash, 2) }}
                        {{ __('keywords.currency') }}</span>
                </div>
                <div class="flex justify-between text-sm border-t pt-3">
                    <span class="text-gray-700 font-medium">{{ __('keywords.balance') }}</span>
                    <span
                        class="font-bold {{ $customer->balance > 0 ? 'text-red-600' : ($customer->balance < 0 ? 'text-emerald-600' : 'text-gray-500') }}">
                        {{ number_format($customer->balance, 2) }} {{ __('keywords.currency') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="xl:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="flex border-b bg-white">
                    <button wire:click="setActiveTab('sales')"
                        class="px-6 py-4 text-sm font-medium transition-colors {{ $activeTab === 'sales' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ __('keywords.sales') }} ({{ $customer->sales()->count() }})
                    </button>
                    <button wire:click="setActiveTab('payments')"
                        class="px-6 py-4 text-sm font-medium transition-colors {{ $activeTab === 'payments' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ __('keywords.payments') }} ({{ $customer->payments()->count() }})
                    </button>
                    <button wire:click="setActiveTab('returns')"
                        class="px-6 py-4 text-sm font-medium transition-colors {{ $activeTab === 'returns' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ __('keywords.sale_returns') }}
                        ({{ $customer->sales()->with('saleReturns')->get()->flatMap(fn($sale) => $sale->saleReturns()->where('cash_refund', false)->pluck('id'))->count() }})
                    </button>
                    <button wire:click="setActiveTab('filters')"
                        class="px-6 py-4 text-sm font-medium transition-colors {{ $activeTab === 'filters' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ __('keywords.filters') }} ({{ $filter ? 1 : 0 }})
                    </button>
                </div>

                @if ($activeTab === 'sales')
                    <div class="overflow-x-auto">
                        @if ($sales->isEmpty())
                            <div class="px-4 py-8 text-center text-sm text-gray-500">
                                {{ __('keywords.no_sales_found') }}</div>
                        @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.number') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.total_price') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.items_count') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.created_at') }}</th>
                                        <th
                                            class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($sales as $sale)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $sale->number }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700 font-medium">
                                                {{ number_format($sale->total_price, 2) }}
                                                {{ __('keywords.currency') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $sale->items->count() }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $sale->created_at->format('Y/m/d H:i') }}</td>
                                            <td class="px-4 py-3 text-end text-sm">
                                                <div class="flex items-center justify-end gap-3">
                                                    <a href="{{ route('sales.show', $sale) }}"
                                                        class="text-blue-600 hover:text-blue-900">
                                                        {{ __('keywords.view') }}
                                                    </a>
                                                    @canany(['manage_sales', 'edit_sales'])
                                                        <a href="{{ route('sales.edit', $sale) }}"
                                                            class="text-emerald-600 hover:text-emerald-700">
                                                            {{ __('keywords.edit') }}
                                                        </a>
                                                    @endcanany
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="border-t border-gray-200 px-5 py-4">{{ $sales->links() }}</div>
                        @endif
                    </div>
                @endif

                @if ($activeTab === 'payments')
                    <div class="overflow-x-auto">
                        @if ($payments->isEmpty())
                            <div class="px-4 py-8 text-center text-sm text-gray-500">
                                {{ __('keywords.no_payments_found') }}</div>
                        @else
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
                                            {{ __('keywords.note') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.sale') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.date') }}</th>
                                        <th
                                            class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($payments as $i => $payment)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $payments->firstItem() + $i - 1 }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-emerald-600">
                                                {{ number_format($payment->amount, 2) }} {{ __('keywords.currency') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $payment->payment_method === 'customer_credit' ? __('keywords.applied_customer_credit') : __('keywords.' . $payment->payment_method) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $payment->note ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $payment->allocations->pluck('sale.number')->filter()->join(', ') ?: '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $payment->created_at->format('Y/m/d H:i') }}</td>
                                            <td class="px-4 py-3 text-end text-sm">
                                                <div class="flex items-center justify-end gap-3">
                                                    <a href="{{ route('customer-payments.print', $payment) }}"
                                                        class="text-blue-600 hover:text-blue-900">
                                                        {{ __('keywords.view') }}
                                                    </a>
                                                    @can('manage_customer_payment_allocations')
                                                        <button type="button"
                                                            wire:click="openEditPayment({{ $payment->id }})"
                                                            class="text-emerald-600 hover:text-emerald-700">
                                                            {{ __('keywords.edit') }}
                                                        </button>
                                                        <button type="button"
                                                            wire:click="setDeletePayment({{ $payment->id }})"
                                                            class="text-red-600 hover:text-red-700">
                                                            {{ __('keywords.delete') }}
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="border-t border-gray-200 px-5 py-4">{{ $payments->links() }}</div>
                        @endif
                    </div>
                @endif

                @if ($activeTab === 'returns')
                    <div class="overflow-x-auto">
                        @if ($returns->isEmpty())
                            <div class="px-4 py-8 text-center text-sm text-gray-500">
                                {{ __('keywords.no_sale_returns_found') }}</div>
                        @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.number') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.sale_number') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.total_return_price') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.reason') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.user') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.created_at') }}</th>
                                        <th
                                            class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($returns as $return)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ $return->number }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $return->sale->number }}
                                            </td>
                                            <td class="px-4 py-3 text-sm font-medium text-amber-600">
                                                {{ number_format($return->total_price, 2) }}
                                                {{ __('keywords.currency') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $return->reason ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $return->user->name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $return->created_at->format('Y/m/d H:i') }}</td>
                                            <td class="px-4 py-3 text-end text-sm">
                                                <div class="flex items-center justify-end gap-3">
                                                    <a href="{{ route('sale-returns.show', $return) }}"
                                                        class="text-blue-600 hover:text-blue-900">
                                                        {{ __('keywords.view') }}
                                                    </a>
                                                    @canany(['manage_sale_returns', 'edit_sale_returns'])
                                                        <a href="{{ route('sale-returns.edit', $return) }}"
                                                            class="text-emerald-600 hover:text-emerald-700">
                                                            {{ __('keywords.edit') }}
                                                        </a>
                                                    @endcanany
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="border-t border-gray-200 px-5 py-4">{{ $returns->links() }}</div>
                        @endif
                    </div>
                @endif

                @if ($activeTab === 'filters')
                    <div class="p-5">
                        @if (!$filter)
                            <div
                                class="rounded-lg border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500">
                                {{ __('keywords.no_filters_found') }}
                            </div>
                        @else
                            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            {{ __('keywords.filter_model') }}
                                        </p>
                                        <h3 class="mt-1 text-lg font-semibold text-gray-900">
                                            {{ $filter->filter_model }}</h3>
                                    </div>

                                    <a href="{{ route('filters.view', $filter) }}"
                                        class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100">
                                        {{ __('keywords.view') }}
                                    </a>
                                </div>

                                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            {{ __('keywords.address') }}</p>
                                        <p class="mt-1 text-sm text-gray-800">{{ $filter->address }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            {{ __('keywords.installed_at') }}</p>
                                        <p class="mt-1 text-sm text-gray-800">
                                            {{ $filter->installed_at?->format('Y/m/d') ?? '—' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            {{ __('keywords.readings_count') }}</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900">
                                            {{ $filter->readings_count }}</p>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            {{ __('keywords.installation_status') }}</p>
                                        <p
                                            class="mt-1 text-sm font-medium {{ $filter->is_installed ? 'text-emerald-700' : 'text-gray-600' }}">
                                            {{ $filter->is_installed ? __('keywords.yes') : __('keywords.no') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @can('manage_customer_payment_allocations')
        <x-modal name="edit-customer-view-payment" title="{{ __('keywords.edit_customer_payment') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input type="number" name="form.amount" label="{{ __('keywords.amount') }}"
                        placeholder="{{ __('keywords.enter_amount') }}" wire:model.blur="form.amount" step="0.01"
                        min="0.01" required />

                    <x-select name="form.payment_method" label="{{ __('keywords.payment_method') }}"
                        wire:model.blur="form.payment_method" :options="$this->paymentMethodOptions" :placeholder="__('keywords.select_payment_method')" required />

                    <x-textarea name="form.note" label="{{ __('keywords.note') }}"
                        placeholder="{{ __('keywords.enter_note') }}" wire:model.blur="form.note" rows="3" />

                    @if ($this->canManageCreatedAt)
                        <x-input type="datetime-local" name="form.created_at" label="{{ __('keywords.created_at') }}"
                            wire:model.blur="form.created_at" />
                    @endif
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-edit-customer-view-payment')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="updatePayment">{{ __('keywords.update') }}</x-button>
            </x-slot:footer>
        </x-modal>

        <x-confirm-modal name="delete-customer-view-payment" title="{{ __('keywords.delete_customer_payment') }}"
            message="{{ __('keywords.delete_customer_payment_confirmation') }}"
            confirmText="{{ __('keywords.delete') }}" variant="danger" />
    @endcan
</div>
