<div class="mx-auto max-w-6xl bg-white p-2">
    <div class="no-print mb-2 flex items-center justify-between border-b pb-1">
        <a href="{{ route('overdue-installments') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-1 text-gray-700 transition-colors hover:bg-gray-200">
            <i class="fas fa-arrow-right"></i>
            <span>{{ __('keywords.back') }}</span>
        </a>
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-1 text-white transition-colors hover:bg-emerald-700">
            <i class="fas fa-print"></i>
            <span>{{ __('keywords.print') }}</span>
        </button>
    </div>

    <div class="mb-2 flex items-end justify-between border-b-2 border-black pb-1">
        <div>
            <h1 class="text-lg font-bold">{{ config('app.name', 'Filter Sales System') }}</h1>
            <p class="text-sm font-semibold">{{ __('keywords.overdue_installments') }}</p>
        </div>
        <div class="text-end text-xs text-gray-600 space-y-1">
            <p><span class="font-bold">طبع بواسطة:</span> {{ auth()->user()?->name ?? '---' }}</p>
            <p><span class="font-bold">التاريخ:</span> <span dir="ltr">{{ now()->format('Y/m/d h:i A') }}</span>
            </p>
        </div>
    </div>

    @if ($overdueSales->isEmpty())
        <div class="rounded-lg border border-gray-300 p-8 text-center text-sm text-gray-600">
            {{ __('keywords.no_overdue_installments') }}
        </div>
    @else
        <div class="mb-2 flex justify-between text-xs font-semibold">
            <div>
                {{ __('keywords.overdue_count') }}: {{ $overdueSales->count() }}
            </div>
            <div>
                {{ __('keywords.total_overdue_installments') }}:
                {{ number_format($overdueSales->sum('installment_amount'), 2) }} {{ __('keywords.currency') }}
            </div>
            <div>
                {{ __('keywords.total_remaining_all') }}:
                {{ number_format($overdueSales->sum('remaining_amount'), 2) }} {{ __('keywords.currency') }}
            </div>
        </div>

        <table class="w-full border-collapse border-2 border-black text-xs leading-tight">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.number') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.customer') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.phone') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.place') }} /
                        {{ __('keywords.address') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">
                        {{ __('keywords.overdue_installment_amount') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.remaining_amount') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.next_installment') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.overdue_since') }}</th>
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
                        <td class="border border-black px-1 py-0.5 align-top">{{ $sale->number }}</td>
                        <td class="border border-black px-1 py-0.5 align-top">
                            {{ $customer?->name ?? '—' }}
                            @if ($customer?->code)
                                <div class="text-xs text-gray-500">{{ $customer->code }}</div>
                            @endif
                        </td>
                        <td class="border border-black px-1 py-0.5 align-top" dir="ltr" style="text-align: right;">
                            {{ implode(' - ', $phones) }}
                        </td>
                        <td class="border border-black px-1 py-0.5 align-top">
                            {{ $customer?->place?->name ?? '—' }}
                            @if ($customer?->address)
                                <div class="text-xs text-gray-500">{{ $customer->address }}</div>
                            @endif
                        </td>
                        <td class="border border-black px-1 py-0.5 align-top font-bold">
                            {{ number_format($sale->installment_amount, 2) }} {{ __('keywords.currency') }}
                        </td>
                        <td class="border border-black px-1 py-0.5 align-top">
                            {{ number_format($sale->remaining_amount, 2) }} {{ __('keywords.currency') }}
                        </td>
                        <td class="border border-black px-1 py-0.5 align-top">
                            {{ $sale->next_installment_date?->format('Y/m/d') ?? '—' }}
                        </td>
                        <td class="border border-black px-1 py-0.5 align-top text-red-600 font-semibold">
                            {{ __('keywords.overdue_days', ['days' => $daysPastDue]) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4 flex justify-around px-4 text-xs break-inside-avoid">

            <div class="text-center">
                <p class="font-bold">توقيع المسئول</p>
                <p class="mt-2">_______________________</p>
            </div>
        </div>
    @endif
</div>
