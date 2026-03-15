<div x-on:confirmed-delete-customer-payment.window="$wire.delete()">
    <x-page-header :title="__('keywords.customer_payments')" :description="__('keywords.customer_payments_management')">
    </x-page-header>

    <x-search-toolbar>
        <x-input name="search" wire:model.live="search" placeholder="{{ __('keywords.search') }}" class="min-w-37.5" />
    </x-search-toolbar>

    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'customer', 'label' => __('keywords.customer')],
        ['key' => 'user', 'label' => __('keywords.user')],
        ['key' => 'purchase', 'label' => __('keywords.sale')],
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
                    <span class="text-sm font-medium text-gray-900">{{ $payment->allocations->pluck('sale.number')->filter()->join(', ') ?: '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-700">{{ $payment->payment_method === 'customer_credit' ? __('keywords.applied_customer_credit') : $payment->payment_method }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-emerald-600 font-medium">
                    {{ number_format($payment->amount, 2) }} {{ __('keywords.currency') }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions
                        :canDelete="auth()->user()->can('manage_customer_payment_allocations')"
                        deleteAction="setDelete({{ $payment->id }})" />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_customer_payments_found')" :colspan="6" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->customerPayments" />

    @can('manage_customer_payment_allocations')
        <x-confirm-modal name="delete-customer-payment" title="{{ __('keywords.delete_customer_payment') }}"
            message="{{ __('keywords.delete_customer_payment_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan
</div>
