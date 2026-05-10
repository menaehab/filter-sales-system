<div x-on:confirmed-delete-customer-payment.window="$wire.delete()">
    <x-page-header :title="__('keywords.customer_payments')" :description="__('keywords.customer_payments_management')">
    </x-page-header>

    <x-search-toolbar>
        <x-input name="search" wire:model.live="search" placeholder="{{ __('keywords.search') }}" class="min-w-37.5" />
        <x-input type="date" name="dateFrom" wire:model.live="dateFrom" class="w-full sm:w-auto"
            placeholder="{{ __('keywords.from_date') }}" />
        <x-input type="date" name="dateTo" wire:model.live="dateTo" class="w-full sm:w-auto"
            placeholder="{{ __('keywords.to_date') }}" />
    </x-search-toolbar>

    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'customer', 'label' => __('keywords.customer')],
        ['key' => 'user', 'label' => __('keywords.user')],
        ['key' => 'purchase', 'label' => __('keywords.sale')],
        ['key' => 'is_down_payment', 'label' => __('keywords.down_payment')],
        ['key' => 'method', 'label' => __('keywords.payment_method')],
        ['key' => 'amount', 'label' => __('keywords.amount')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->customerPayments as $payment)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $payment->customer?->name ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-900">{{ $payment->user?->name ?? '—' }}</span>
                </td>
                <td class="px-4 py-3">
                    <span
                        class="text-sm font-medium text-gray-900">{{ $payment->allocations->pluck('sale.number')->filter()->join(', ') ?: '—' }}</span>
                </td>
                <td class="px-4 py-3">
                    @if ($payment->is_down_payment)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ __('keywords.down_payment') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ __('keywords.installment') }}
                        </span>
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span
                        class="text-sm text-gray-700">{{ $payment->payment_method === 'customer_credit' ? __('keywords.applied_customer_credit') : __('keywords.' . $payment->payment_method) }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-emerald-600 font-medium">
                    {{ number_format($payment->amount, 2) }} {{ __('keywords.currency') }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions editAction="openEdit({{ $payment->id }})" :canEdit="auth()->user()->can('manage_customer_payment_allocations')" :canDelete="auth()->user()->can('manage_customer_payment_allocations')"
                        deleteAction="setDelete({{ $payment->id }})" />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_customer_payments_found')" :colspan="6" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->customerPayments" />

    @can('manage_customer_payment_allocations')
        <x-modal name="edit-customer-payment" title="{{ __('keywords.edit_customer_payment') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input type="number" name="form.amount" label="{{ __('keywords.amount') }}"
                        placeholder="{{ __('keywords.enter_amount') }}" wire:model.blur="form.amount" step="0.01"
                        min="0.01" required />

                    <x-select name="form.payment_method" label="{{ __('keywords.payment_method') }}"
                        wire:model.blur="form.payment_method" :options="$this->paymentMethodOptions" :placeholder="__('keywords.select_payment_method')" required />

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_down_payment" wire:model.blur="form.is_down_payment" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <label for="is_down_payment" class="text-sm text-gray-700">{{ __('keywords.down_payment') }}</label>
                    </div>

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
                    @click="$dispatch('close-modal-edit-customer-payment')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="updateCustomerPayment">{{ __('keywords.update') }}</x-button>
            </x-slot:footer>
        </x-modal>

        <x-confirm-modal name="delete-customer-payment" title="{{ __('keywords.delete_customer_payment') }}"
            message="{{ __('keywords.delete_customer_payment_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan
</div>
