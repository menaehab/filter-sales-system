<div class="max-w-4xl mx-auto p-8 bg-white">
    {{-- Print and Back Buttons --}}
    <div class="no-print flex items-center justify-between mb-6 border-b pb-4">
        <a href="{{ route('purchases') }}"
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
            <p class="text-sm">{{ __('keywords.purchase_invoice') }}</p>
        </div>

        {{-- Invoice Info Grid --}}
        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div>
                <p class="mb-1"><strong>{{ __('keywords.invoice_number') }}:</strong> {{ $purchase->number }}</p>
                <p class="mb-1"><strong>{{ __('keywords.date') }}:</strong>
                    {{ $purchase->created_at->format('Y-m-d') }}</p>
                <p class="mb-1"><strong>{{ __('keywords.time') }}:</strong>
                    {{ $purchase->created_at->format('H:i') }}</p>
            </div>
            <div>
                <p class="mb-1"><strong>{{ __('keywords.supplier') }}:</strong>
                    {{ $purchase->supplier?->name ?? $purchase->supplier_name }}</p>
                @if ($purchase->supplier)
                    @if ($purchase->supplier->phone_numbers !== [])
                        <p class="mb-1"><strong>{{ __('keywords.phone') }}:</strong>
                            {{ implode(' - ', $purchase->supplier->phone_numbers) }}</p>
                    @endif
                    <p class="mb-1 font-semibold">
                        <strong>{{ __('keywords.supplier_balance') }}:</strong>
                        <span class="{{ $purchase->supplier->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format($purchase->supplier->balance, 2) }} {{ __('keywords.currency') }}
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
                    <th class="border border-black px-3 py-2 text-center">{{ __('keywords.quantity') }}</th>
                    <th class="border border-black px-3 py-2 text-end">{{ __('keywords.cost_price') }}</th>
                    <th class="border border-black px-3 py-2 text-end">{{ __('keywords.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $index => $item)
                    <tr>
                        <td class="border border-black px-3 py-2">{{ $index + 1 }}</td>
                        <td class="border border-black px-3 py-2">{{ $item->product?->name ?? $item->product_name }}
                        </td>
                        <td class="border border-black px-3 py-2 text-center">
                            {{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td class="border border-black px-3 py-2 text-end">{{ number_format($item->cost_price, 2) }}
                        </td>
                        <td class="border border-black px-3 py-2 text-end font-semibold">
                            {{ number_format($item->cost_price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Payment Summary --}}
        <div class="border-t-2 border-black pt-4">
            <div class="flex justify-end">
                <div class="w-64 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>{{ __('keywords.total_price') }}:</span>
                        <span class="font-semibold">{{ number_format($purchase->total_price, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    <div class="flex justify-between text-green-700">
                        <span>{{ __('keywords.paid_amount') }}:</span>
                        <span class="font-semibold">{{ number_format($purchase->paid_amount, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    <div
                        class="flex justify-between border-t-2 border-black pt-2 {{ $purchase->remaining_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                        <span class="font-bold">{{ __('keywords.remaining_amount') }}:</span>
                        <span class="font-bold text-lg">{{ number_format($purchase->remaining_amount, 2) }}
                            {{ __('keywords.currency') }}</span>
                    </div>
                    @if ($purchase->isInstallment())
                        <div class="flex justify-between border-t border-gray-300 pt-2 text-blue-600">
                            <span>{{ __('keywords.monthly_installment') }}:</span>
                            <span class="font-semibold">{{ number_format($purchase->installment_amount, 2) }}
                                {{ __('keywords.currency') }}</span>
                        </div>
                        <div class="flex justify-between text-blue-600">
                            <span>{{ __('keywords.installment_months') }}:</span>
                            <span class="font-semibold">{{ $purchase->installment_months }}
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
