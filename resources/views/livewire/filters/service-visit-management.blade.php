<div>
    <x-page-header :title="__('keywords.service_visits')" :description="__('keywords.service_visits_management')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('service-visits.print.pending') }}" target="_blank">
                <i class="fas fa-print text-xs"></i>
                {{ __('keywords.print_pending_visits') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-search-toolbar>
        <x-select name="completionStatus" wire:model.live="completionStatus" :options="[
            'completed' => __('keywords.completed_visits'),
            'pending' => __('keywords.pending_visits'),
        ]"
            placeholder="{{ __('keywords.all_statuses') }}" class="min-w-50" />
    </x-search-toolbar>

    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['key' => 'customer_code', 'label' => __('keywords.code')],
        ['key' => 'customer_name', 'label' => __('keywords.customer')],
        ['key' => 'phone', 'label' => __('keywords.phone')],
        ['key' => 'address', 'label' => __('keywords.address')],
        ['key' => 'maintenance_type', 'label' => __('keywords.maintenance_type')],
        ['key' => 'cost', 'label' => __('keywords.maintenance_cost')],
        ['key' => 'technician', 'label' => __('keywords.technician_name')],
        ['key' => 'notes', 'label' => __('keywords.notes')],
        ['key' => 'status', 'label' => __('keywords.status')],
        ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
    ]">
        @forelse ($visits as $visit)
            @php
                $customer = $visit->waterFilter?->customer;
            @endphp
            <tr class="transition-colors hover:bg-gray-50">
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-700">{{ $customer?->code ?? '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $customer?->name ?? $visit->user_name }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-600">
                        {{ $customer?->phone_numbers !== [] ? implode(' - ', $customer->phone_numbers) : '—' }}
                    </span>
                </td>
                <td class="max-w-55 px-4 py-3">
                    <span class="line-clamp-2 text-sm text-gray-700">{{ $visit->waterFilter?->address ?? '—' }}</span>
                </td>
                <td class="max-w-65 px-4 py-3">
                    <span class="line-clamp-2 text-sm text-gray-900">{{ $visit->maintenance_type ?: '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-700">
                        {{ $visit->cost !== null ? number_format((float) $visit->cost, 2) . ' ' . __('keywords.currency') : '—' }}
                    </span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <span class="text-sm text-gray-700">{{ $visit->technician_name ?: '—' }}</span>
                </td>
                <td class="max-w-60 px-4 py-3">
                    <span class="line-clamp-2 text-sm text-gray-600">{{ $visit->notes ?: '—' }}</span>
                </td>
                <td class="whitespace-nowrap px-4 py-3">
                    <x-badge :label="$visit->is_completed ? __('keywords.completed') : __('keywords.pending')" :color="$visit->is_completed ? 'green' : 'amber'" />
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('service-visits.show', $visit) }}"
                            class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-sky-50 hover:text-sky-600"
                            title="{{ __('keywords.view') }}">
                            <i class="fas fa-eye text-sm"></i>
                        </a>
                        @can('manage_service_visits')
                            @if (!$visit->is_completed)
                                <button type="button" wire:click="markCompleted({{ $visit->id }})"
                                    class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-emerald-50 hover:text-emerald-600"
                                    title="{{ __('keywords.mark_visit_completed') }}">
                                    <i class="fas fa-check text-sm"></i>
                                </button>
                            @endif
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <x-empty-state :title="__('keywords.no_service_visits_found')" :colspan="10" />
        @endforelse
    </x-data-table>

    <x-pagination-info :paginator="$visits" />
</div>
