<div x-on:confirmed-delete-supplier-payment-allocation.window="$wire.delete()">
    <x-page-header :title="__('keywords.supplier_payment_allocations')" :description="__('keywords.supplier_payment_allocations_management')">
    </x-page-header>

        <x-search-toolbar>
            <x-input name="search" wire:model.live="search" placeholder="{{ __('keywords.search') }}" class="min-w-37.5" />
        </x-search-toolbar>

        {{-- Supplier Payment Allocations table --}}
        <x-data-table :searchable="false" :paginated="false" :headers="[
            ['key' => 'supplier', 'label' => __('keywords.supplier')],
            ['key' => 'user', 'label' => __('keywords.user')],
            ['key' => 'purchase', 'label' => __('keywords.purchase')],
            ['key' => 'amount', 'label' => __('keywords.amount')],
            ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
        ]">
            @forelse ($this->supplierPaymentAllocations as $allocation)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="text-sm font-medium text-gray-900">{{ $allocation->supplierPayment?->supplier?->name ?? '—' }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="text-sm text-gray-900">{{ $allocation->supplierPayment?->user?->name ?? '—' }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="text-sm font-medium text-gray-900">{{ $allocation->purchase?->number ?? '—' }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-emerald-600 font-medium">
                        {{ number_format($allocation->amount, 2) }} {{ __('keywords.currency') }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                        <x-table-actions
                            :canDelete="auth()->user()->can('manage_supplier_payment_allocations')"
                            deleteAction="setDelete({{ $allocation->id }})" />
                    </td>
                </tr>
            @empty
                <x-empty-state :title="__('keywords.no_supplier_payment_allocations_found')" :colspan="5" />
            @endforelse
        </x-data-table>

        <x-pagination-info :paginator="$this->supplierPaymentAllocations" />
        @can('manage_supplier_payment_allocations')

            {{-- Delete Confirmation Modal --}}
            <x-confirm-modal name="delete-supplier-payment-allocation" title="{{ __('keywords.delete_supplier_payment_allocation') }}"
                message="{{ __('keywords.delete_supplier_payment_allocation_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
                variant="danger" />
        @endcan

    </div>
