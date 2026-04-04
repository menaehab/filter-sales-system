<div x-on:confirmed-delete-purchase-return.window="$wire.delete()">
    <x-page-header :title="__('keywords.purchase_returns')" :description="__('keywords.purchase_returns_management')">
        @canany(['manage_purchase_returns', 'add_purchase_returns'])
            <x-slot:actions>
                <x-button variant="primary" href="{{ route('purchase-returns.create') }}">
                    <i class="fas fa-plus text-xs"></i>
                    {{ __('keywords.add_purchase_return') }}
                </x-button>
            </x-slot:actions>
        @endcanany
    </x-page-header>

    <x-search-toolbar>
        <x-input type="date" name="dateFrom" wire:model.live="dateFrom" class="w-full sm:w-auto"
            placeholder="{{ __('keywords.from_date') }}" />
        <x-input type="date" name="dateTo" wire:model.live="dateTo" class="w-full sm:w-auto"
            placeholder="{{ __('keywords.to_date') }}" />
    </x-search-toolbar>

    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'number', 'label' => __('keywords.number')],
        ['key' => 'purchase', 'label' => __('keywords.purchase_number')],
        ['key' => 'total', 'label' => __('keywords.total_return_price')],
        ['key' => 'cash_refund', 'label' => __('keywords.cash_refund')],
        ['key' => 'user', 'label' => __('keywords.user')],
        ['key' => 'date', 'label' => __('keywords.created_at')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($this->purchaseReturns as $return)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">{{ $return->id }}</td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $return->number }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <a href="{{ route('purchases.show', $return->purchase) }}"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                        {{ $return->purchase->number }}
                    </a>
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-red-600 font-medium">
                    {{ number_format($return->total_price, 2) }} {{ __('keywords.currency') }}
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    @if ($return->cash_refund)
                        <x-badge :label="__('keywords.yes')" color="green" />
                    @else
                        <x-badge :label="__('keywords.no')" color="red" />
                    @endif
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700">
                    {{ $return->user?->name ?? '—' }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                    {{ $return->created_at->format('Y-m-d') }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <div class="flex items-center justify-end gap-1">
                        @canany(['manage_purchase_returns', 'view_purchase_returns'])
                            <a href="{{ route('purchase-returns.show', $return) }}"
                                class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-sky-50 hover:text-sky-600 transition-colors"
                                title="{{ __('keywords.view') }}">
                                <img src="/images/icons/view.svg" alt="" class="h-4 w-4" aria-hidden="true">
                                <span class="text-xs font-medium">{{ __('keywords.view') }}</span>
                            </a>
                        @endcanany

                        @canany(['manage_purchase_returns', 'edit_purchase_returns'])
                            <a href="{{ route('purchase-returns.edit', $return) }}"
                                class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-sky-50 hover:text-sky-600 transition-colors"
                                title="{{ __('keywords.edit') }}">
                                <img src="/images/icons/edit.svg" alt="" class="h-4 w-4" aria-hidden="true">
                                <span class="text-xs font-medium">{{ __('keywords.edit') }}</span>
                            </a>
                        @endcanany

                        @can('manage_purchase_returns')
                            <button wire:click="setDelete({{ $return->id }})"
                                class="inline-flex h-8 items-center gap-1 rounded-lg px-2 text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors"
                                title="{{ __('keywords.delete') }}">
                                <img src="/images/icons/delete.svg" alt="" class="h-4 w-4" aria-hidden="true">
                                <span class="text-xs font-medium">{{ __('keywords.delete') }}</span>
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_purchase_returns_found')" :colspan="8" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$this->purchaseReturns" />

    <x-confirm-modal name="delete-purchase-return" title="{{ __('keywords.delete_purchase_return') }}"
        message="{{ __('keywords.delete_purchase_return_confirmation') }}" confirmText="{{ __('keywords.delete') }}"
        variant="danger" />
</div>
