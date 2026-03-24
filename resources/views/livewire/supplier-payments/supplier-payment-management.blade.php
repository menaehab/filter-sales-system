<div x-on:confirmed-delete-supplier-payment.window="$wire.delete()">
    <x-page-header :title="__('keywords.supplier_payments')" :description="__('keywords.supplier_payments_management')">
    </x-page-header>

    <x-search-toolbar>
        <x-input name="search" wire:model.live="search" placeholder="{{ __('keywords.search') }}" class="min-w-37.5" />
        <x-input type="date" name="dateFrom" wire:model.live="dateFrom" class="w-full sm:w-auto"
            placeholder="{{ __('keywords.from_date') }}" />
        <x-input type="date" name="dateTo" wire:model.live="dateTo" class="w-full sm:w-auto"
            placeholder="{{ __('keywords.to_date') }}" />
    </x-search-toolbar>

    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'supplier', 'label' => __('keywords.supplier')],
        ['key' => 'user', 'label' => __('keywords.user')],
        ['key' => 'purchase', 'label' => __('keywords.purchase')],
        ['key' => 'method', 'label' => __('keywords.payment_method')],
        ['key' => 'amount', 'label' => __('keywords.amount')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->supplierPayments as $payment)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $payment->supplier?->name ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-900">{{ $payment->user?->name ?? '—' }}</span>
                </td>
                <td class="px-4 py-3">
                    <span
                        class="text-sm font-medium text-gray-900">{{ $payment->allocations->pluck('purchase.number')->filter()->join(', ') ?: '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span
                        class="text-sm text-gray-700">{{ $payment->payment_method === 'supplier_credit' ? __('keywords.applied_supplier_credit') : __('keywords.' . $payment->payment_method) }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-emerald-600 font-medium">
                    {{ number_format($payment->amount, 2) }} {{ __('keywords.currency') }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <x-table-actions :canDelete="auth()->user()->can('manage_supplier_payment_allocations')" deleteAction="setDelete({{ $payment->id }})" />
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_supplier_payments_found')" :colspan="6" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->supplierPayments" />

    @can('manage_supplier_payment_allocations')
        <x-confirm-modal name="delete-supplier-payment" title="{{ __('keywords.delete_supplier_payment') }}"
            message="{{ __('keywords.delete_supplier_payment_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
            variant="danger" />
    @endcan
</div>
