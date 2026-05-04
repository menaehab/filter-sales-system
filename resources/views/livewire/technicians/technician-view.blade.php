<div class="space-y-6">
    <x-page-header :title="$technician->name" :description="__('keywords.technician_details')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('technicians') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_technicians') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-stat-card 
            :label="__('keywords.maintenances_count')" 
            :value="$technician->maintenances()->count()" 
            color="emerald" 
            iconClass="fas fa-wrench" 
        />
        <x-stat-card 
            :label="__('keywords.service_visits_count')" 
            :value="$technician->serviceVisits()->count()" 
            color="blue" 
            iconClass="fas fa-calendar-check" 
        />
        <x-stat-card 
            :label="__('keywords.water_readings_count')" 
            :value="$technician->waterReadings()->count()" 
            color="purple" 
            iconClass="fas fa-flask" 
        />
    </div>

    {{-- Tabs --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
            <div class="flex flex-wrap items-center gap-2">
                <x-button :variant="$activeTab === 'maintenances' ? 'primary' : 'ghost'" wire:click="setActiveTab('maintenances')" size="sm">
                    {{ __('keywords.maintenances') }}
                </x-button>
                <x-button :variant="$activeTab === 'service_visits' ? 'primary' : 'ghost'" wire:click="setActiveTab('service_visits')" size="sm">
                    {{ __('keywords.service_visits') }}
                </x-button>
                <x-button :variant="$activeTab === 'readings' ? 'primary' : 'ghost'" wire:click="setActiveTab('readings')" size="sm">
                    {{ __('keywords.water_readings') }}
                </x-button>
            </div>
        </div>

        <div class="p-6">
            @if ($activeTab === 'maintenances')
                <x-data-table :searchable="false" :paginated="false" :headers="[
                    ['key' => 'customer', 'label' => __('keywords.customer')],
                    ['key' => 'date', 'label' => __('keywords.date')],
                    ['key' => 'cost', 'label' => __('keywords.maintenance_cost')],
                    ['key' => 'description', 'label' => __('keywords.description')],
                ]">
                    @forelse ($maintenances as $maintenance)
                        <tr wire:key="maintenance-{{ $maintenance->id }}" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $maintenance->filter->customer->name ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ $maintenance->created_at->format('Y/m/d H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-emerald-600">
                                {{ number_format($maintenance->cost, 2) }} {{ __('keywords.currency') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $maintenance->description ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :title="__('keywords.no_data_found')" :colspan="4" />
                    @endforelse
                </x-data-table>
                <x-pagination-info :paginator="$maintenances" />
            @elseif ($activeTab === 'service_visits')
                <x-data-table :searchable="false" :paginated="false" :headers="[
                    ['key' => 'customer', 'label' => __('keywords.customer')],
                    ['key' => 'date', 'label' => __('keywords.date')],
                    ['key' => 'type', 'label' => __('keywords.maintenance_type')],
                    ['key' => 'cost', 'label' => __('keywords.cost')],
                    ['key' => 'status', 'label' => __('keywords.status')],
                ]">
                    @forelse ($serviceVisits as $visit)
                        <tr wire:key="visit-{{ $visit->id }}" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $visit->waterFilter->customer->name ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ $visit->created_at->format('Y/m/d H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                                {{ $visit->maintenance_type ?: '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-emerald-600">
                                {{ number_format($visit->cost, 2) }} {{ __('keywords.currency') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <x-badge :label="$visit->is_completed ? __('keywords.completed') : __('keywords.pending')" 
                                    :color="$visit->is_completed ? 'green' : 'amber'" />
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :title="__('keywords.no_data_found')" :colspan="5" />
                    @endforelse
                </x-data-table>
                <x-pagination-info :paginator="$serviceVisits" />
            @elseif ($activeTab === 'readings')
                <x-data-table :searchable="false" :paginated="false" :headers="[
                    ['key' => 'customer', 'label' => __('keywords.customer')],
                    ['key' => 'date', 'label' => __('keywords.date')],
                    ['key' => 'tds', 'label' => __('keywords.tds')],
                    ['key' => 'quality', 'label' => __('keywords.water_quality')],
                    ['key' => 'type', 'label' => __('keywords.type')],
                ]">
                    @forelse ($waterReadings as $reading)
                        <tr wire:key="reading-{{ $reading->id }}" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">
                                {{ $reading->waterFilter->customer->name ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ $reading->created_at->format('Y/m/d H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-sky-600">
                                {{ $reading->tds }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <x-badge :label="__('keywords.' . $reading->water_quality)" 
                                    :color="$reading->water_quality === 'normal' ? 'blue' : ($reading->water_quality === 'good' ? 'green' : 'red')" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ $reading->before_installment ? __('keywords.before_installment') : __('keywords.after_installment') }}
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :title="__('keywords.no_data_found')" :colspan="5" />
                    @endforelse
                </x-data-table>
                <x-pagination-info :paginator="$waterReadings" />
            @endif
        </div>
    </div>
</div>
