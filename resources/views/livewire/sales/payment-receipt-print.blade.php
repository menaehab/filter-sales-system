<div class="max-w-3xl mx-auto p-8 bg-white">
    {{-- Print Button و Back Button --}}
    <div class="no-print flex items-center justify-between mb-6 border-b pb-4">
        <a href="{{ route('sales') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
            <i class="fas fa-arrow-right"></i>
            <span>{{ __('keywords.back') }}</span>
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors">
            <i class="fas fa-print"></i>
            <span>{{ __('keywords.print') }}</span>
        </button>
    </div>

    {{-- Receipt Header --}}
    <div class="border-2 border-black p-6">
        <div class="text-center mb-6 border-b-2 border-black pb-4">
            <h1 class="text-3xl font-bold mb-2">{{ config('app.name', 'Filter Sales System') }}</h1>
            <p class="text-xl font-semibold">{{ __('keywords.payment_receipt') }}</p>
        </div>

        {{-- Receipt Info Grid --}}
        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div>
                <p class="mb-2"><strong>{{ __('keywords.receipt_number') }}:</strong> {{ $payment->id }}</p>
                <p class="mb-2"><strong>{{ __('keywords.date') }}:</strong> {{ $payment->created_at->format('Y-m-d') }}</p>
                <p class="mb-2"><strong>{{ __('keywords.time') }}:</strong> {{ $payment->created_at->format('H:i') }}</p>
            </div>
            <div>
                <p class="mb-2"><strong>{{ __('keywords.customer') }}:</strong> {{ $payment->customer?->name ?? '—' }}</p>
                @if($payment->customer?->phone)
                    <p class="mb-2"><strong>{{ __('keywords.phone') }}:</strong> {{ $payment->customer->phone }}</p>
                @endif
                <p class="mb-2"><strong>{{ __('keywords.payment_method') }}:</strong>
                    @switch($payment->payment_method)
                        @case('cash')
                            {{ __('keywords.cash') }}
                            @break
                        @case('bank_transfer')
                            {{ __('keywords.bank_transfer') }}
                            @break
                        @case('customer_credit')
                            {{ __('keywords.customer_credit') }}
                            @break
                        @default
                            {{ $payment->payment_method }}
                    @endswitch
                </p>
            </div>
        </div>

        {{-- Payment Amount Box --}}
        <div class="border-2 border-black bg-gray-50 p-6 mb-6 text-center">
            <p class="text-sm text-gray-600 mb-2">{{ __('keywords.paid_amount') }}</p>
            <p class="text-4xl font-bold text-green-600">{{ number_format($payment->amount, 2) }} {{ __('keywords.currency') }}</p>
        </div>

        {{-- Allocations Table --}}
        @if($payment->allocations->count() > 0)
            <div class="mb-6">
                <h3 class="text-sm font-semibold mb-3 border-b border-black pb-2">{{ __('keywords.payment_allocations') }}</h3>
                <table class="w-full border-collapse border border-black text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-black px-3 py-2 text-start">{{ __('keywords.invoice_number') }}</th>
                            <th class="border border-black px-3 py-2 text-end">{{ __('keywords.invoice_total') }}</th>
                            <th class="border border-black px-3 py-2 text-end">{{ __('keywords.paid_amount') }}</th>
                            <th class="border border-black px-3 py-2 text-end">{{ __('keywords.remaining_amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->allocations as $allocation)
                            @if($allocation->sale)
                                <tr>
                                    <td class="border border-black px-3 py-2">{{ $allocation->sale->number }}</td>
                                    <td class="border border-black px-3 py-2 text-end">{{ number_format($allocation->sale->total_price, 2) }}</td>
                                    <td class="border border-black px-3 py-2 text-end font-semibold text-green-600">{{ number_format($allocation->amount, 2) }}</td>
                                    <td class="border border-black px-3 py-2 text-end {{ $allocation->sale->remaining_amount > 0 ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                        {{ number_format($allocation->sale->remaining_amount, 2) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Note --}}
        @if($payment->note)
            <div class="mb-6 p-3 bg-gray-50 border border-gray-300 rounded-lg">
                <p class="text-sm"><strong>{{ __('keywords.note') }}:</strong> {{ $payment->note }}</p>
            </div>
        @endif

        {{-- Customer Balance --}}
        @if($payment->customer)
            <div class="border-t-2 border-black pt-4 mb-4">
                <div class="text-center">
                    <p class="text-sm text-gray-600 mb-1">{{ __('keywords.customer_balance') }}</p>
                    <p class="text-2xl font-bold {{ $payment->customer->balance < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($payment->customer->balance, 2) }} {{ __('keywords.currency') }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Signatures --}}
        <div class="grid grid-cols-2 gap-8 mt-8 pt-6 border-t border-black text-sm">
            <div class="text-center">
                <div class="border-t border-gray-400 pt-2 mt-16">
                    <p class="font-semibold">{{ __('keywords.customer_signature') }}</p>
                </div>
            </div>
            <div class="text-center">
                <div class="border-t border-gray-400 pt-2 mt-16">
                    <p class="font-semibold">{{ __('keywords.company_signature') }}</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 pt-4 border-t border-black text-center text-xs text-gray-600">
            <p>{{ __('keywords.printed_on') }}: {{ now()->format('Y-m-d H:i') }}</p>
            <p class="mt-1">{{ __('keywords.printed_by') }}: {{ auth()->user()->name }}</p>
        </div>
    </div>
</div>
