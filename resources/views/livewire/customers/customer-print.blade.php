<div class="mx-auto max-w-6xl bg-white p-2">
    <div class="no-print mb-2 flex items-center justify-between border-b pb-1">
        <a href="{{ route('customers') }}"
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
            <p class="text-sm font-semibold">{{ __('keywords.customers') }}</p>
        </div>
        <div class="text-end text-xs text-gray-600 space-y-1">
            <p><span class="font-bold">طبع بواسطة:</span> {{ auth()->user()?->name ?? '---' }}</p>
            <p><span class="font-bold">التاريخ:</span> <span dir="ltr">{{ now()->format('Y/m/d h:i A') }}</span></p>
            <p><span class="font-bold">عدد العملاء:</span> {{ $customers->count() }}</p>
        </div>
    </div>

    @if ($customers->isEmpty())
        <div class="rounded-lg border border-gray-300 p-8 text-center text-sm text-gray-600">
            {{ __('keywords.no_customers_found') }}
        </div>
    @else
        <table class="w-full border-collapse border-2 border-black text-xs leading-tight">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-black px-1 py-0.5 text-start">#</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.code') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.name') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.address') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.phone') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.national_number') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.place') }}</th>
                    <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.balance') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $index => $customer)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                        <td class="border border-black px-1 py-0.5 align-top text-gray-500">{{ $index + 1 }}</td>
                        <td class="border border-black px-1 py-0.5 align-top font-semibold">{{ $customer->code ?? '—' }}</td>
                        <td class="border border-black px-1 py-0.5 align-top font-medium">{{ $customer->name }}</td>
                        <td class="border border-black px-1 py-0.5 align-top">{{ $customer->address ?? '—' }}</td>
                        <td class="border border-black px-1 py-0.5 align-top" dir="ltr" style="text-align: right;">
                            {{ $customer->phone_numbers !== [] ? implode(' - ', $customer->phone_numbers) : '—' }}
                        </td>
                        <td class="border border-black px-1 py-0.5 align-top" dir="ltr">{{ $customer->national_number ?? '—' }}</td>
                        <td class="border border-black px-1 py-0.5 align-top">{{ $customer->place?->name ?? '—' }}</td>
                        <td class="border border-black px-1 py-0.5 align-top font-semibold {{ $customer->balance >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                            {{ number_format($customer->balance, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 font-bold">
                    <td colspan="7" class="border border-black px-1 py-0.5 text-end">{{ __('keywords.total') }}</td>
                    <td class="border border-black px-1 py-0.5">
                        {{ number_format($customers->sum('balance'), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-4 flex justify-around px-4 text-xs break-inside-avoid">
            <div class="text-center">
                <p class="font-bold">توقيع المسئول</p>
                <p class="mt-2">_______________________</p>
            </div>
        </div>
    @endif
</div>
