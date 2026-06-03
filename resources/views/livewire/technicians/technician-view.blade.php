<div class="space-y-6">
    <x-page-header :title="$technician->name" :description="__('keywords.technician_details')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('technicians') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_technicians') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card :label="__('keywords.maintenances_count')" :value="$technician->maintenances()->count()" color="emerald" iconClass="fas fa-wrench" />
        <x-stat-card :label="__('keywords.service_visits_count')" :value="$technician->serviceVisits()->count()" color="blue" iconClass="fas fa-calendar-check" />
        <x-stat-card :label="__('keywords.water_readings_count')" :value="$technician->waterReadings()->count()" color="purple" iconClass="fas fa-flask" />
        
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ __('keywords.collected_payments') }}</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-gray-900">{{ $collectedPaymentsStats['count'] }}</p>
                    <p class="text-sm text-gray-500">({{ number_format($collectedPaymentsStats['total'], 2) }} {{ __('keywords.currency') }})</p>
                </div>
            </div>
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm flex flex-wrap gap-4 items-end">
        <div class="space-y-1">
            <label
                class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $activeTab === 'maintenances' ? __('keywords.replacement_date_from') : __('keywords.from_date') }}</label>
            <x-input name="dateFrom" type="date" wire:model.live="dateFrom" class="w-full sm:w-48" />
        </div>
        <div class="space-y-1">
            <label
                class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $activeTab === 'maintenances' ? __('keywords.replacement_date_to') : __('keywords.to_date') }}</label>
            <x-input name="dateTo" type="date" wire:model.live="dateTo" class="w-full sm:w-48" />
        </div>
        <x-button variant="secondary" wire:click="$set('dateFrom', null); $set('dateTo', null);" size="sm"
            class="mb-1">
            <i class="fas fa-undo text-xs"></i>
            {{ __('keywords.reset') }}
        </x-button>
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
                <x-button :variant="$activeTab === 'installed_filters' ? 'primary' : 'ghost'" wire:click="setActiveTab('installed_filters')" size="sm">
                    الفلاتر التي تم تركيبها ({{ $installedFilters->total() }})
                </x-button>
                <x-button :variant="$activeTab === 'customer_payments' ? 'primary' : 'ghost'" wire:click="setActiveTab('customer_payments')" size="sm">
                    {{ __('keywords.collected_payments') }} ({{ $customerPayments->total() }})
                </x-button>
            </div>
        </div>

        <div class="p-6">
            @if ($activeTab === 'maintenances')
                <x-data-table :searchable="false" :paginated="false" :headers="[
                    ['key' => 'customer', 'label' => __('keywords.customer')],
                    ['key' => 'date', 'label' => __('keywords.replaced_at')],
                    ['key' => 'details', 'label' => __('keywords.details')],
                    ['key' => 'cost', 'label' => __('keywords.maintenance_cost')],
                    ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
                ]">
                    @forelse ($maintenances as $maintenance)
                        <tr wire:key="maintenance-{{ $maintenance->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-bold text-gray-900">{{ $maintenance->filter?->customer?->name ?? '—' }}</span>
                                    <span
                                        class="text-xs text-gray-500">{{ $maintenance->filter?->filter_model ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ $maintenance->created_at->format('Y/m/d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="space-y-1">
                                    @if ($maintenance->candleChanges->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                class="text-xs font-semibold text-gray-400 uppercase tracking-tighter">{{ __('keywords.candles') }}:</span>
                                            @foreach ($maintenance->candleChanges as $change)
                                                <x-badge :label="$change->candle_name" color="blue" size="xs" />
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($maintenance->items->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                class="text-xs font-semibold text-gray-400 uppercase tracking-tighter">{{ __('keywords.installed_items') }}:</span>
                                            @foreach ($maintenance->items as $mItem)
                                                <span
                                                    class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                                    {{ $mItem->saleItem->product->name ?? '—' }}
                                                    ({{ $mItem->quantity }})
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($maintenance->description)
                                        <p class="text-xs text-gray-500 italic">{{ $maintenance->description }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-emerald-600">
                                {{ number_format($maintenance->cost, 2) }} {{ __('keywords.currency') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($maintenance->filter?->customer)
                                        <x-button variant="ghost" size="xs"
                                            href="{{ route('customers.view', $maintenance->filter->customer) }}"
                                            title="{{ __('keywords.view_customer') }}">
                                            <i class="fas fa-user"></i>
                                        </x-button>
                                    @endif
                                    @if ($maintenance->filter)
                                        <x-button variant="ghost" size="xs"
                                            href="{{ route('filters.view', $maintenance->filter) }}"
                                            title="{{ __('keywords.view_filter') }}">
                                            <i class="fas fa-filter"></i>
                                        </x-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :title="__('keywords.no_data_found')" :colspan="5" />
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
                    ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
                ]">
                    @forelse ($serviceVisits as $visit)
                        <tr wire:key="visit-{{ $visit->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-bold text-gray-900">{{ $visit->waterFilter?->customer?->name ?? '—' }}</span>
                                    <span
                                        class="text-xs text-gray-500">{{ $visit->waterFilter?->filter_model ?? '—' }}</span>
                                </div>
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
                                <x-badge :label="$visit->is_completed ? __('keywords.completed') : __('keywords.pending')" :color="$visit->is_completed ? 'green' : 'amber'" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($visit->waterFilter?->customer)
                                        <x-button variant="ghost" size="xs"
                                            href="{{ route('customers.view', $visit->waterFilter->customer) }}"
                                            title="{{ __('keywords.view_customer') }}">
                                            <i class="fas fa-user"></i>
                                        </x-button>
                                    @endif
                                    @if ($visit->waterFilter)
                                        <x-button variant="ghost" size="xs"
                                            href="{{ route('filters.view', $visit->waterFilter) }}"
                                            title="{{ __('keywords.view_filter') }}">
                                            <i class="fas fa-filter"></i>
                                        </x-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :title="__('keywords.no_data_found')" :colspan="6" />
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
                    ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
                ]">
                    @forelse ($waterReadings as $reading)
                        <tr wire:key="reading-{{ $reading->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-bold text-gray-900">{{ $reading->waterFilter?->customer?->name ?? '—' }}</span>
                                    <span
                                        class="text-xs text-gray-500">{{ $reading->waterFilter?->filter_model ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ $reading->created_at->format('Y/m/d H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-sky-600">
                                {{ $reading->tds }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                <x-badge :label="__('keywords.' . $reading->water_quality)" :color="$reading->water_quality === 'normal' ? 'blue' : ($reading->water_quality === 'good' ? 'green' : 'red')" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ $reading->before_installment ? __('keywords.before_installment') : __('keywords.after_installment') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($reading->waterFilter?->customer)
                                        <x-button variant="ghost" size="xs"
                                            href="{{ route('customers.view', $reading->waterFilter->customer) }}"
                                            title="{{ __('keywords.view_customer') }}">
                                            <i class="fas fa-user"></i>
                                        </x-button>
                                    @endif
                                    @if ($reading->waterFilter)
                                        <x-button variant="ghost" size="xs"
                                            href="{{ route('filters.view', $reading->waterFilter) }}"
                                            title="{{ __('keywords.view_filter') }}">
                                            <i class="fas fa-filter"></i>
                                        </x-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :title="__('keywords.no_data_found')" :colspan="6" />
                    @endforelse
                </x-data-table>
                <x-pagination-info :paginator="$waterReadings" />
            @elseif ($activeTab === 'installed_filters')
                <x-data-table :searchable="false" :paginated="false" :headers="[
                    ['key' => 'customer', 'label' => __('keywords.customer')],
                    ['key' => 'date', 'label' => __('keywords.date')],
                    ['key' => 'model', 'label' => __('keywords.filter_model')],
                    ['key' => 'actions', 'label' => __('keywords.actions'), 'align' => 'right'],
                ]">
                    @forelse ($installedFilters as $installedFilter)
                        <tr wire:key="installed-filter-{{ $installedFilter->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span
                                        class="text-sm font-bold text-gray-900">{{ $installedFilter->customer?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ $installedFilter->installed_at ? $installedFilter->installed_at->format('Y/m/d') : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                                {{ $installedFilter->filter_model }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-end text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($installedFilter->customer)
                                        <x-button variant="ghost" size="xs"
                                            href="{{ route('customers.view', $installedFilter->customer) }}"
                                            title="{{ __('keywords.view_customer') }}">
                                            <i class="fas fa-user"></i>
                                        </x-button>
                                    @endif
                                    <x-button variant="ghost" size="xs"
                                        href="{{ route('filters.view', $installedFilter) }}"
                                        title="{{ __('keywords.view_filter') }}">
                                        <i class="fas fa-filter"></i>
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :title="__('keywords.no_data_found')" :colspan="4" />
                    @endforelse
                </x-data-table>
                <x-pagination-info :paginator="$installedFilters" />
            @elseif ($activeTab === 'customer_payments')
                <x-data-table :searchable="false" :paginated="false" :headers="[
                    ['key' => 'customer', 'label' => __('keywords.customer')],
                    ['key' => 'date', 'label' => __('keywords.date')],
                    ['key' => 'amount', 'label' => __('keywords.amount')],
                    ['key' => 'method', 'label' => __('keywords.payment_method')],
                    ['key' => 'note', 'label' => __('keywords.note')],
                ]">
                    @forelse ($customerPayments as $payment)
                        <tr wire:key="payment-{{ $payment->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900">{{ $payment->customer?->name ?? '—' }}</span>
                                    <span class="text-xs text-gray-500">{{ $payment->allocations->pluck('sale.number')->filter()->join(', ') ?: '—' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">
                                {{ $payment->created_at->format('Y/m/d H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-emerald-600">
                                {{ number_format($payment->amount, 2) }} {{ __('keywords.currency') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">
                                {{ __('keywords.' . $payment->payment_method) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $payment->note ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <x-empty-state :title="__('keywords.no_data_found')" :colspan="5" />
                    @endforelse
                </x-data-table>
                <x-pagination-info :paginator="$customerPayments" />
            @endif
        </div>
    </div>
</div>
