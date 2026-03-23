<div>
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
                    <span class="text-gray-500">{{ __('keywords.phone') }}</span>
                    <span
                        class="font-medium text-gray-900">{{ $customer->phone ?? __('keywords.not_specified') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.created_at') }}</span>
                    <span class="font-medium text-gray-900">{{ $customer->created_at?->format('Y-m-d H:i') }}</span>
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
                        {{ __('keywords.filters') }} ({{ $customer->waterFilters()->count() }})
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
                                                {{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                            <td class="px-4 py-3 text-end text-sm">
                                                <a href="{{ route('sales.show', $sale) }}"
                                                    class="text-blue-600 hover:text-blue-900">
                                                    {{ __('keywords.view') }}
                                                </a>
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
                                                {{ $payment->payment_method === 'customer_credit' ? __('keywords.applied_customer_credit') : $payment->payment_method }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $payment->note ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $payment->allocations->pluck('sale.number')->filter()->join(', ') ?: '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $payment->created_at->format('Y-m-d H:i') }}</td>
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
                                                {{ $return->created_at->format('Y-m-d H:i') }}</td>
                                            <td class="px-4 py-3 text-end text-sm">
                                                <a href="{{ route('sale-returns.show', $return) }}"
                                                    class="text-blue-600 hover:text-blue-900">
                                                    {{ __('keywords.view') }}
                                                </a>
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
                    <div class="overflow-x-auto">
                        @if ($filters->isEmpty())
                            <div class="px-4 py-8 text-center text-sm text-gray-500">
                                {{ __('keywords.no_filters_found') }}</div>
                        @else
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.filter_model') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.address') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.installed_at') }}</th>
                                        <th
                                            class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.readings_count') }}</th>
                                        <th
                                            class="px-4 py-3 text-end text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($filters as $filter)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ $filter->filter_model }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $filter->address }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $filter->installed_at?->format('Y-m-d') ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $filter->readings()->count() }}</td>
                                            <td class="px-4 py-3 text-end text-sm">
                                                <a href="{{ route('filters.view', $filter) }}"
                                                    class="text-blue-600 hover:text-blue-900">
                                                    {{ __('keywords.view') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="border-t border-gray-200 px-5 py-4">{{ $filters->links() }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
