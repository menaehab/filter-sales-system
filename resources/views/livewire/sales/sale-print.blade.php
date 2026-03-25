<div class="max-w-4xl mx-auto p-8 bg-white">
    {{-- Print Button & Back Button --}}
    <div class="no-print flex items-center justify-between mb-6 border-b pb-4">
        <a href="{{ url()->previous() }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
            <i class="fas fa-arrow-right"></i>
            <span>{{ __('keywords.back') }}</span>
        </a>
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
            <i class="fas fa-print"></i>
            <span>{{ __('keywords.print') }}</span>
        </button>
    </div>

    {{-- Invoice Header --}}
    <div class="border-2 border-black p-6">
        <div class="text-center mb-6 border-b-2 border-black pb-4">
            <h1 class="text-3xl font-bold mb-2">{{ config('app.name', 'Filter Sales System') }}</h1>
            <p class="text-sm">{{ __('keywords.sales_invoice') }}</p>
        </div>

        {{-- Invoice Info Grid --}}
        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div>
                <p class="mb-1"><strong>{{ __('keywords.invoice_number') }}:</strong> {{ $sale->number }}</p>
                <p class="mb-1"><strong>{{ __('keywords.date') }}:</strong> {{ $sale->created_at->format('Y-m-d') }}
                </p>
                <p class="mb-1"><strong>{{ __('keywords.time') }}:</strong> {{ $sale->created_at->format('H:i') }}
                </p>
                @if ($sale->dealer_name)
                    <p class="mb-1"><strong>{{ __('keywords.dealer_name') }}:</strong> {{ $sale->dealer_name }}</p>
                @endif
            </div>
            <div>
                <p class="mb-1"><strong>{{ __('keywords.customer') }}:</strong> {{ $sale->customer?->name ?? '—' }}
                </p>
                @if ($sale->customer)
                    @if ($sale->customer->phone)
                        <p class="mb-1"><strong>{{ __('keywords.phone') }}:</strong> {{ $sale->customer->phone }}
                        </p>
                    @endif
                    @if ($sale->customer->address)
                        <p class="mb-1"><strong>{{ __('keywords.address') }}:</strong>
                            {{ $sale->customer->address }}</p>
                    @endif
                    <p class="mb-1 font-semibold">
                        <strong>{{ __('keywords.customer_balance') }}:</strong>
                        <span class="{{ $sale->customer->balance < 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($sale->customer->balance, 2) }} {{ __('keywords.currency') }}
                        </span>
                    </p>
                @endif
            </div>
        </div>

        {{-- Items Table --}}
        <table class="w-full border-collapse border-2 border-black mb-6 text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-black px-3 py-2 text-start">#</th>
                    <th class="border border-black px-3 py-2 text-start">{{ __('keywords.product') }}</th>
                    <th class="border border-black px-3 py-2 text-start">{{ __('keywords.quantity') }}</th>
                    <th class="border border-black px-3 py-2 text-start">{{ __('keywords.price') }}</th>
                    <th class="border border-black px-3 py-2 text-start">{{ __('keywords.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $index => $item)
                    <tr>
                        <td class="border border-black px-3 py-2">{{ $index + 1 }}</td>
                        <td class="border border-black px-3 py-2">
                            {{ $item->product?->name ?? __('keywords.product_deleted') }}</td>
                        <td class="border border-black px-3 py-2 text-center">
                            {{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td class="border border-black px-3 py-2 text-start">{{ number_format($item->sell_price, 2) }}
                        </td>
                        <td class="border border-black px-3 py-2 text-start font-semibold">
                            {{ number_format($item->sell_price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Payment Summary --}}
        <div class="border-t-2 border-black pt-4">
            <div class="flex justify-end">
                <div class="w-64 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>{{ __('keywords.subtotal') }}:</span>
                        <span class="font-semibold">{{ number_format($sale->items_subtotal, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    @if ($sale->with_vat)
                        <div class="flex justify-between">
                            <span>{{ __('keywords.vat_amount') }} (14%):</span>
                            <span class="font-semibold">+ {{ number_format($sale->vat_amount, 2) }}
                                {{ __('keywords.currency') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span>{{ __('keywords.total_after_vat') }}:</span>
                        <span class="font-semibold">{{ number_format($sale->subtotal_after_vat, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    @if ($sale->isInstallment() && (float) ($sale->interest_rate ?: 0) > 0)
                        <div class="flex justify-between">
                            <span>{{ __('keywords.interest_amount') }}
                                ({{ rtrim(rtrim(number_format((float) $sale->interest_rate, 2), '0'), '.') }}%):</span>
                            <span class="font-semibold">+ {{ number_format($sale->interest_amount, 2) }}
                                {{ __('keywords.currency') }}</span>
                        </div>
                    @endif
                    @if ($sale->installment_surcharge_total > 0)
                        <div class="flex justify-between">
                            <span>{{ __('keywords.installment_monthly_fee') }}:</span>
                            <span class="font-semibold">+ {{ number_format($sale->installment_surcharge_total, 2) }}
                                {{ __('keywords.currency') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span>{{ __('keywords.invoice_total') }}:</span>
                        <span class="font-semibold">{{ number_format($sale->total_price, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    <div class="flex justify-between text-green-700">
                        <span>{{ __('keywords.paid_amount') }}:</span>
                        <span class="font-semibold">{{ number_format($sale->paid_amount, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    <div
                        class="flex justify-between border-t-2 border-black pt-2 {{ $sale->remaining_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                        <span class="font-bold">{{ __('keywords.remaining_amount') }}:</span>
                        <span class="font-bold text-lg">{{ number_format($sale->remaining_amount, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    @if ($sale->isInstallment())
                        <div class="flex justify-between border-t border-gray-300 pt-2 text-blue-600">
                            <span>{{ __('keywords.monthly_installment') }}:</span>
                            <span class="font-semibold">{{ number_format($sale->installment_amount, 2) }}
                                {{ __('keywords.currency') }}</span>
                        </div>
                        <div class="flex justify-between text-blue-600">
                            <span>{{ __('keywords.installment_months') }}:</span>
                            <span class="font-semibold">{{ $sale->installment_months }}
                                {{ __('keywords.month') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-8 pt-4 border-t border-black text-center text-xs text-gray-600">
            <p>{{ __('keywords.printed_on') }}: {{ now()->format('Y-m-d H:i') }}</p>
            <p class="mt-1">{{ __('keywords.printed_by') }}: {{ auth()->user()->name }}</p>
        </div>
    </div>
</div>
