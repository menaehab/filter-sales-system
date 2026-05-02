<div class="mx-auto max-w-6xl bg-white p-8">
        <div class="no-print mb-6 flex items-center justify-between border-b pb-4">
            <a href="{{ route('overdue-installments') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-gray-700 transition-colors hover:bg-gray-200">
                <i class="fas fa-arrow-right"></i>
                <span>{{ __('keywords.back') }}</span>
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2 text-white transition-colors hover:bg-emerald-700">
                <i class="fas fa-print"></i>
                <span>{{ __('keywords.print') }}</span>
            </button>
        </div>

        <div class="mb-6 border-b-2 border-black pb-4 text-center">
            <h1 class="mb-2 text-2xl font-bold">{{ config('app.name', 'Filter Sales System') }}</h1>
            <p class="text-lg font-semibold">{{ __('keywords.overdue_installments') }}</p>
            <p class="mt-1 text-sm text-gray-600">{{ now()->format('Y/m/d H:i') }}</p>
        </div>

        @if ($overdueSales->isEmpty())
            <div class="rounded-lg border border-gray-300 p-8 text-center text-sm text-gray-600">
                {{ __('keywords.no_overdue_installments') }}
            </div>
        @else
            <div class="mb-4 flex justify-between text-sm font-semibold">
                <div>
                    {{ __('keywords.overdue_count') }}: {{ $overdueSales->count() }}
                </div>
                <div>
                    {{ __('keywords.total_overdue_installments') }}: {{ number_format($overdueSales->sum('installment_amount'), 2) }} {{ __('keywords.currency') }}
                </div>
                <div>
                    {{ __('keywords.total_remaining_all') }}: {{ number_format($overdueSales->sum('remaining_amount'), 2) }} {{ __('keywords.currency') }}
                </div>
            </div>

            <table class="w-full border-collapse border-2 border-black text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.number') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.customer') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.phone') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.place') }} / {{ __('keywords.address') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.overdue_installment_amount') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.remaining_amount') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.next_installment') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.overdue_since') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($overdueSales as $sale)
                        @php
                            $customer = $sale->customer;
                            $phones = $customer?->phone_numbers ?? [];
                            $daysPastDue = $sale->next_installment_date
                                ? (int) $sale->next_installment_date->diffInDays(now())
                                : 0;
                        @endphp
                        <tr>
                            <td class="border border-black px-2 py-2">{{ $sale->number }}</td>
                            <td class="border border-black px-2 py-2">
                                {{ $customer?->name ?? '—' }}
                                @if($customer?->code)
                                    <div class="text-xs text-gray-500">{{ $customer->code }}</div>
                                @endif
                            </td>
                            <td class="border border-black px-2 py-2" dir="ltr" style="text-align: right;">
                                {{ implode(' - ', $phones) }}
                            </td>
                            <td class="border border-black px-2 py-2">
                                {{ $customer?->place?->name ?? '—' }}
                                @if($customer?->address)
                                    <div class="text-xs text-gray-500">{{ $customer->address }}</div>
                                @endif
                            </td>
                            <td class="border border-black px-2 py-2 font-bold">
                                {{ number_format($sale->installment_amount, 2) }} {{ __('keywords.currency') }}
                            </td>
                            <td class="border border-black px-2 py-2">
                                {{ number_format($sale->remaining_amount, 2) }} {{ __('keywords.currency') }}
                            </td>
                            <td class="border border-black px-2 py-2">
                                {{ $sale->next_installment_date?->format('Y/m/d') ?? '—' }}
                            </td>
                            <td class="border border-black px-2 py-2 text-red-600 font-semibold">
                                {{ __('keywords.overdue_days', ['days' => $daysPastDue]) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
