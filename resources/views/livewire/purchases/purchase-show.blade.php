<div>
    <x-page-header :title="__('keywords.purchase_details')" :description="__('keywords.purchase_details_description')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('purchases') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_purchases') }}
            </x-button>
            <x-button variant="primary" href="{{ route('purchases.edit', $purchase) }}">
                <i class="fas fa-pen-to-square text-xs"></i>
                {{ __('keywords.edit_purchase') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.purchase_info') }}</h3>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">#</span>
                    <span class="font-medium text-gray-900">{{ $purchase->id }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.supplier') }}</span>
                    <span class="font-medium text-gray-900">{{ $purchase->supplier_name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.user') }}</span>
                    <span class="font-medium text-gray-900">{{ $purchase->user_name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.created_at') }}</span>
                    <span class="font-medium text-gray-900">{{ $purchase->created_at->format('Y-m-d H:i') }}</span>
                </div>

                <div class="border-t pt-3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.total_price') }}</span>
                        <span class="font-bold text-gray-900">{{ number_format($purchase->total_price, 2) }} {{ __('keywords.currency') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.paid_amount') }}</span>
                        <span class="font-medium text-emerald-600">{{ number_format($purchase->paid_amount, 2) }} {{ __('keywords.currency') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.remaining_amount') }}</span>
                        <span class="font-medium {{ $purchase->remaining_amount > 0 ? 'text-red-600' : 'text-gray-500' }}">{{ number_format($purchase->remaining_amount, 2) }} {{ __('keywords.currency') }}</span>
                    </div>
                </div>

                <div class="border-t pt-3">
                    @if($purchase->isInstallment())
                        <x-badge :label="__('keywords.installment')" color="blue" />
                    @else
                        <x-badge :label="__('keywords.cash')" color="green" />
                    @endif

                    @if($purchase->isFullyPaid())
                        <x-badge :label="__('keywords.fully_paid')" color="green" class="ms-2" />
                    @elseif($purchase->paid_amount > 0)
                        <x-badge :label="__('keywords.partially_paid')" color="yellow" class="ms-2" />
                    @else
                        <x-badge :label="__('keywords.unpaid')" color="red" class="ms-2" />
                    @endif
                </div>
            </div>

            @if($purchase->isInstallment())
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-3">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.installment_details') }}</h3>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.down_payment') }}</span>
                        <span class="font-medium text-gray-900">{{ number_format($purchase->down_payment, 2) }} {{ __('keywords.currency') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.installment_value') }}</span>
                        <span class="font-medium text-blue-600">{{ number_format($purchase->installment_amount, 2) }} {{ __('keywords.currency') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.installment_months') }}</span>
                        <span class="font-medium text-gray-900">{{ $purchase->installment_months }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ __('keywords.next_installment') }}</span>
                        <span class="font-medium {{ $purchase->next_installment_date && $purchase->next_installment_date->lte(now()) ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $purchase->next_installment_date?->format('Y-m-d') ?? '—' }}
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <div class="xl:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.purchase_items') }}</h3>
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
                            @foreach($purchase->items as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->product_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ number_format($item->cost_price, 2) }} {{ __('keywords.currency') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ number_format($item->cost_price * $item->quantity, 2) }} {{ __('keywords.currency') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.payment_history') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">#</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.amount') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.payment_method') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.note') }}</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('keywords.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($purchase->paymentAllocations as $i => $allocation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-emerald-600">{{ number_format($allocation->amount, 2) }} {{ __('keywords.currency') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $allocation->supplierPayment?->payment_method ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $allocation->supplierPayment?->note ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $allocation->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('keywords.no_payments_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
