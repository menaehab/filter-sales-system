<x-layouts.print :title="__('keywords.service_visits')" orientation="landscape">
    <div class="mx-auto max-w-6xl bg-white p-8">
        <div class="no-print mb-6 flex items-center justify-between border-b pb-4">
            <a href="{{ route('service-visits') }}"
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
            <p class="text-sm">{{ __('keywords.pending_visits') }}</p>
            <p class="mt-1 text-xs text-gray-600">{{ now()->format('Y-m-d H:i') }}</p>
        </div>

        @if ($visits->isEmpty())
            <div class="rounded-lg border border-gray-300 p-8 text-center text-sm text-gray-600">
                {{ __('keywords.no_service_visits_found') }}
            </div>
        @else
            <table class="w-full border-collapse border-2 border-black text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.code') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.customer') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.phone') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.address') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.maintenance_type') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.maintenance_cost') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.technician_name') }}</th>
                        <th class="border border-black px-2 py-2 text-start">{{ __('keywords.notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($visits as $visit)
                        @php
                            $customer = $visit->waterFilter?->customer;
                        @endphp
                        <tr>
                            <td class="border border-black px-2 py-2">{{ $customer?->code ?? '' }}</td>
                            <td class="border border-black px-2 py-2">{{ $customer?->name ?? $visit->user_name }}</td>
                            <td class="border border-black px-2 py-2">
                                {{ $customer?->phone_numbers !== [] ? implode(' - ', $customer->phone_numbers) : '' }}
                            </td>
                            <td class="border border-black px-2 py-2">{{ $visit->waterFilter?->address ?? '' }}</td>
                            <td class="border border-black px-2 py-2">{{ $visit->maintenance_type ?: '' }}</td>
                            <td class="border border-black px-2 py-2">
                                {{ $visit->cost !== null ? number_format((float) $visit->cost, 2) . ' ' . __('keywords.currency') : '' }}
                            </td>
                            <td class="border border-black px-2 py-2">{{ $visit->technician_name ?: '' }}</td>
                            <td class="border border-black px-2 py-2">{{ $visit->notes ?: '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.print>
