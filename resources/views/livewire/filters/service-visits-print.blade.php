<x-layouts.print :title="__('keywords.service_visits')" orientation="landscape">
    <div class="mx-auto max-w-6xl bg-white p-2">
        <div class="no-print mb-2 flex items-center justify-between border-b pb-1">
            <a href="{{ route('service-visits') }}"
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
                <p class="text-sm font-semibold">{{ __('keywords.pending_visits') }}</p>
            </div>
            <div class="text-end text-xs text-gray-600 space-y-1">
                <p><span class="font-bold">طبع بواسطة:</span> {{ auth()->user()?->name ?? '---' }}</p>
                <p><span class="font-bold">التاريخ:</span> <span
                        dir="ltr">{{ now()->format('Y/m/d h:i A') }}</span></p>
            </div>
        </div>

        @if ($visits->isEmpty())
            <div class="rounded-lg border border-gray-300 p-8 text-center text-sm text-gray-600">
                {{ __('keywords.no_service_visits_found') }}
            </div>
        @else
            <table class="w-full border-collapse border-2 border-black text-xs leading-tight">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.code') }}</th>
                        <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.customer') }}</th>
                        <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.phone') }}</th>
                        <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.address') }}</th>
                        <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.maintenance_type') }}
                        </th>
                        <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.maintenance_cost') }}
                        </th>
                        <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.technician_name') }}
                        </th>
                        <th class="border border-black px-1 py-0.5 text-start">{{ __('keywords.notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($visits as $visit)
                        @php
                            $customer = $visit->waterFilter?->customer;
                        @endphp
                        <tr>
                            <td class="border border-black px-1 py-0.5 align-top">{{ $customer?->code ?? '' }}</td>
                            <td class="border border-black px-1 py-0.5 align-top">
                                {{ $customer?->name ?? $visit->user_name }}</td>
                            <td class="border border-black px-1 py-0.5 align-top">
                                {{ $customer?->phone_numbers !== [] ? implode(' - ', $customer->phone_numbers) : '' }}
                            </td>
                            <td class="border border-black px-1 py-0.5 align-top">
                                {{ $visit->waterFilter?->address ?? '' }}</td>
                            <td class="border border-black px-1 py-0.5 align-top">{{ $visit->maintenance_type ?: '' }}
                            </td>
                            <td class="border border-black px-1 py-0.5 align-top">
                                {{ $visit->cost !== null ? number_format((float) $visit->cost, 2) . ' ' . __('keywords.currency') : '' }}
                            </td>
                            <td class="border border-black px-1 py-0.5 align-top">{{ $visit->technician_name ?: '' }}
                            </td>
                            <td class="border border-black px-1 py-0.5 align-top">{{ $visit->notes ?: '' }}</td>
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
</x-layouts.print>
