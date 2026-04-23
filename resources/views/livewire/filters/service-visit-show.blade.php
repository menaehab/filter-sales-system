<x-details-page :title="__('keywords.service_visit_details')" :subtitle="$serviceVisit->maintenance_type ?: '—'" :back-url="route('service-visits')" :back-label="__('keywords.back_to_service_visits')" :badge="$serviceVisit->is_completed ? __('keywords.completed') : __('keywords.pending')"
    :badge-color="$serviceVisit->is_completed ? 'green' : 'amber'">

    <x-slot:actions>
        @can('manage_service_visits')
            @if (!$serviceVisit->is_completed)
                <x-button variant="primary" wire:click="markCompleted">
                    <i class="fas fa-check text-xs"></i>
                    {{ __('keywords.mark_visit_completed') }}
                </x-button>
            @endif
        @endcan
    </x-slot:actions>

    <div class="border-b border-gray-100 px-5 py-4 sm:px-6">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
            {{ __('keywords.service_visit_information') }}
        </h3>
    </div>

    <dl class="divide-y divide-gray-100">
        <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">{{ __('keywords.maintenance_type') }}</dt>
            <dd class="text-sm font-semibold text-gray-900 sm:col-span-2">{{ $serviceVisit->maintenance_type ?: '—' }}
            </dd>
        </div>
        <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">{{ __('keywords.technician_name') }}</dt>
            <dd class="text-sm text-gray-900 sm:col-span-2">{{ $serviceVisit->technician_name ?: '—' }}</dd>
        </div>
        <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">{{ __('keywords.maintenance_cost') }}</dt>
            <dd class="text-sm text-gray-900 sm:col-span-2">
                {{ $serviceVisit->cost !== null ? number_format((float) $serviceVisit->cost, 2) . ' ' . __('keywords.currency') : '—' }}
            </dd>
        </div>
        <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">{{ __('keywords.notes') }}</dt>
            <dd class="text-sm text-gray-900 sm:col-span-2">{{ $serviceVisit->notes ?: '—' }}</dd>
        </div>
        <div class="grid gap-2 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-gray-500">{{ __('keywords.created_at') }}</dt>
            <dd class="text-sm text-gray-900 sm:col-span-2">{{ $serviceVisit->created_at?->format('Y/m/d H:i') }}</dd>
        </div>
    </dl>

    <x-slot:aside>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    {{ __('keywords.customer_info') }}
                </h3>
            </div>
            @php
                $customer = $serviceVisit->waterFilter?->customer;
            @endphp
            <dl class="divide-y divide-gray-100">
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.customer') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $customer?->name ?? $serviceVisit->user_name }}
                    </dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.code') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $customer?->code ?? '—' }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.phone') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">
                        {{ $customer?->phone_numbers !== [] ? implode(' - ', $customer->phone_numbers) : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.address') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ $serviceVisit->waterFilter?->address ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    {{ __('keywords.filter_details') }}
                </h3>
            </div>
            <dl class="divide-y divide-gray-100">
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.filter_model') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">
                        {{ $serviceVisit->waterFilter?->filter_model ?? '—' }}</dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.installation_status') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">
                        {{ $serviceVisit->waterFilter?->is_installed ? __('keywords.yes') : __('keywords.no') }}
                    </dd>
                </div>
                <div class="flex justify-between px-5 py-3">
                    <dt class="text-sm text-gray-500">{{ __('keywords.installed_at') }}</dt>
                    <dd class="text-sm font-medium text-gray-900">
                        {{ $serviceVisit->waterFilter?->installed_at?->format('Y/m/d') ?? '—' }}
                    </dd>
                </div>
                <div class="px-5 py-3">
                    <a href="{{ $serviceVisit->waterFilter ? route('filters.view', $serviceVisit->waterFilter) : '#' }}"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-700 {{ $serviceVisit->waterFilter ? '' : 'pointer-events-none opacity-50' }}">
                        {{ __('keywords.view_filter_details') }}
                    </a>
                </div>
            </dl>
        </div>
    </x-slot:aside>
</x-details-page>
