<div>
    <x-page-header :title="__('keywords.sale_return_details')" :description="__('keywords.sale_return_details_description')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('sale-returns') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_sale_returns') }}
            </x-button>
            <x-button variant="primary" href="{{ route('sale-returns.edit', $saleReturn) }}">
                <i class="fas fa-pen-to-square text-xs"></i>
                {{ __('keywords.edit_sale_return') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.return_info') }}</h3>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.number') }}</span>
                    <span class="font-medium text-gray-900">{{ $saleReturn->number }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.sale_number') }}</span>
                    <a href="{{ route('sales.show', $saleReturn->sale) }}" class="font-medium text-emerald-600 hover:text-emerald-700">
                        {{ $saleReturn->sale->number }}
                    </a>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.customer') }}</span>
                    <span class="font-medium text-gray-900">{{ $saleReturn->sale->customer?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.user') }}</span>
                    <span class="font-medium text-gray-900">{{ $saleReturn->user?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.created_at') }}</span>
                    <span class="font-medium text-gray-900">{{ $saleReturn->created_at->format('Y-m-d H:i') }}</span>
                </div>

                <div class="border-t pt-3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.total_return_price') }}</span>
                        <span class="font-bold text-red-600">{{ number_format($saleReturn->total_price, 2) }} {{ __('keywords.currency') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.cash_refund') }}</span>
                        @if($saleReturn->cash_refund)
                            <x-badge :label="__('keywords.yes')" color="green" />
                        @else
                            <x-badge :label="__('keywords.no')" color="red" />
                        @endif
                    </div>
                </div>

                @if($saleReturn->reason)
                    <div class="border-t pt-3">
                        <span class="text-sm text-gray-500">{{ __('keywords.reason') }}</span>
                        <p class="text-sm text-gray-900 mt-1">{{ $saleReturn->reason }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="xl:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.return_items') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.product') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.sell_price') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.quantity') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.total_price') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($saleReturn->items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->product?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ number_format($item->sell_price, 2) }} {{ __('keywords.currency') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-red-600">{{ number_format($item->sell_price * $item->quantity, 2) }} {{ __('keywords.currency') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
