<div>
    <x-page-header :title="__('keywords.purchase_return_details')" :description="__('keywords.purchase_return_details_description')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('purchase-returns') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_purchase_returns') }}
            </x-button>
            <x-button variant="primary" href="{{ route('purchase-returns.edit', $purchaseReturn) }}">
                <i class="fas fa-pen-to-square text-xs"></i>
                {{ __('keywords.edit_purchase_return') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.return_info') }}</h3>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.number') }}</span>
                    <span class="font-medium text-gray-900">{{ $purchaseReturn->number }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.purchase_number') }}</span>
                    <a href="{{ route('purchases.show', $purchaseReturn->purchase) }}" class="font-medium text-emerald-600 hover:text-emerald-700">
                        {{ $purchaseReturn->purchase->number }}
                    </a>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.supplier') }}</span>
                    <span class="font-medium text-gray-900">{{ $purchaseReturn->purchase->supplier_name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.user') }}</span>
                    <span class="font-medium text-gray-900">{{ $purchaseReturn->user?->name ?? '—' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.created_at') }}</span>
                    <span class="font-medium text-gray-900">{{ $purchaseReturn->created_at->format('Y-m-d H:i') }}</span>
                </div>

                <div class="border-t pt-3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.total_return_price') }}</span>
                        <span class="font-bold text-red-600">{{ number_format($purchaseReturn->total_price, 2) }} {{ __('keywords.currency') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.cash_refund') }}</span>
                        @if($purchaseReturn->cash_refund)
                            <x-badge :label="__('keywords.yes')" color="green" />
                        @else
                            <x-badge :label="__('keywords.no')" color="red" />
                        @endif
                    </div>
                </div>

                @if($purchaseReturn->reason)
                    <div class="border-t pt-3">
                        <span class="text-sm text-gray-500">{{ __('keywords.reason') }}</span>
                        <p class="text-sm text-gray-900 mt-1">{{ $purchaseReturn->reason }}</p>
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
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.cost_price') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.quantity') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.total_price') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($purchaseReturn->items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->product?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ number_format($item->cost_price, 2) }} {{ __('keywords.currency') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-red-600">{{ number_format($item->cost_price * $item->quantity, 2) }} {{ __('keywords.currency') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
