<div>
    <x-page-header :title="$filter->filter_model" :description="__('keywords.filter_details')">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('filters') }}">
                <i class="fas fa-arrow-right text-xs"></i>
                {{ __('keywords.back_to_filters') }}
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Filter Info Sidebar --}}
        <div class="xl:col-span-1 space-y-6">
            {{-- Filter Details Card --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.filter_details') }}</h3>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.filter_model') }}</span>
                    <span class="font-medium text-gray-900">{{ $filter->filter_model }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.address') }}</span>
                    <span class="font-medium text-gray-900">{{ $filter->address }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.customer') }}</span>
                    <a href="{{ route('customers.view', $filter->customer) }}"
                        class="font-medium text-blue-600 hover:text-blue-800">
                        {{ $filter->customer?->name }}
                    </a>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.phone') }}</span>
                    <span class="font-medium text-gray-900">
                        {{ $filter->customer?->phone_numbers !== [] ? implode(' - ', $filter->customer->phone_numbers) : '—' }}
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.installation_status') }}</span>
                    <span class="font-medium text-gray-900">
                        {{ $filter->is_installed ? __('keywords.yes') : __('keywords.no') }}
                    </span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.installed_at') }}</span>
                    <span
                        class="font-medium text-gray-900">{{ $filter->is_installed ? $filter->installed_at?->format('Y-m-d') ?? '—' : __('keywords.not_installed') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.created_at') }}</span>
                    <span class="font-medium text-gray-900">{{ $filter->created_at?->format('Y-m-d H:i') }}</span>
                </div>
            </div>

            {{-- Candles Status Cards --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.candles_status') }}</h3>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ __('keywords.candle_ok') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            {{ __('keywords.candle_due_soon') }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            {{ __('keywords.candle_needs_replacement') }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @foreach ($candles as $candle)
                        <div @class([
                            'rounded-lg border p-3 cursor-pointer transition-all hover:shadow-md',
                            'border-emerald-200 bg-emerald-50' => $candle['status'] === 'success',
                            'border-amber-200 bg-amber-50' => $candle['status'] === 'warning',
                            'border-red-200 bg-red-50' => $candle['status'] === 'danger',
                            'border-gray-200 bg-gray-50' => $candle['status'] === 'unknown',
                        ]) wire:click="openMarkCandle('{{ $candle['key'] }}')"
                            title="{{ __('keywords.click_to_record_maintenance') }}">
                            <div class="flex items-center justify-between mb-2">
                                <span @class([
                                    'text-sm font-medium',
                                    'text-emerald-700' => $candle['status'] === 'success',
                                    'text-amber-700' => $candle['status'] === 'warning',
                                    'text-red-700' => $candle['status'] === 'danger',
                                    'text-gray-600' => $candle['status'] === 'unknown',
                                ])>{{ $candle['name'] }}</span>
                                <span @class([
                                    'w-3 h-3 rounded-full',
                                    'bg-emerald-500' => $candle['status'] === 'success',
                                    'bg-amber-500' => $candle['status'] === 'warning',
                                    'bg-red-500' => $candle['status'] === 'danger',
                                    'bg-gray-400' => $candle['status'] === 'unknown',
                                ])></span>
                            </div>
                            <div class="text-xs text-gray-500 space-y-1">
                                <div>{{ __('keywords.interval') }}: {{ $candle['interval'] }}</div>
                                @if ($candle['replaced_at'])
                                    <div>{{ __('keywords.last_replaced') }}:
                                        {{ $candle['replaced_at']->diffForHumans() }}</div>
                                @endif
                                @if ($candle['next_date'])
                                    <div>{{ __('keywords.next_date') }}: {{ $candle['next_date']->format('Y-m-d') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Readings Table --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="flex items-center justify-between border-b bg-white px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.water_readings') }}
                        ({{ $filter->readings()->count() }})</h3>
                    @can('manage_water_filters')
                        <x-button variant="primary" size="sm" wire:click="openAddReading">
                            <i class="fas fa-plus text-xs"></i>
                            {{ __('keywords.add_reading') }}
                        </x-button>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    @if ($readings->isEmpty())
                        <div class="px-4 py-8 text-center text-sm text-gray-500">
                            {{ __('keywords.no_water_readings_found') }}</div>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        {{ __('keywords.technician_name') }}</th>
                                    <th
                                        class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        {{ __('keywords.tds') }}</th>
                                    <th
                                        class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        {{ __('keywords.water_quality') }}</th>
                                    <th
                                        class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        {{ __('keywords.type') }}</th>
                                    <th
                                        class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        {{ __('keywords.date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($readings as $reading)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $reading->technician_name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $reading->tds }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span @class([
                                                'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
                                                'bg-emerald-100 text-emerald-700' => $reading->water_quality === 'good',
                                                'bg-amber-100 text-amber-700' => $reading->water_quality === 'fair',
                                                'bg-red-100 text-red-700' => $reading->water_quality === 'poor',
                                            ])>
                                                {{ __('keywords.' . $reading->water_quality) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($reading->before_installment)
                                                <span
                                                    class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">
                                                    {{ __('keywords.before_installment') }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                                    {{ __('keywords.after_installment') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $reading->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="border-t border-gray-200 px-5 py-4">{{ $readings->links() }}</div>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b bg-white px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.maintenance_history') }}</h3>
                </div>

                @if ($maintenances->isEmpty())
                    <div class="px-5 py-8 text-center text-sm text-gray-500">
                        {{ __('keywords.no_maintenance_history') }}
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach ($maintenances as $maintenance)
                            @php
                                $changedCandles = $maintenance->candleChanges->pluck('candle_name')->unique()->values();
                                $latestReplacedAt = $maintenance->candleChanges->sortByDesc('replaced_at')->first()
                                    ?->replaced_at;
                                $installedProducts = $maintenance->items
                                    ->groupBy(
                                        fn($item) => $item->saleItem?->product?->name ?? __('keywords.not_specified'),
                                    )
                                    ->map(fn($items) => (int) $items->sum('quantity'));
                            @endphp

                            <div class="p-5 space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="text-sm text-gray-500">
                                        {{ __('keywords.replaced_at') }}:
                                        <span class="font-medium text-gray-900">
                                            {{ $latestReplacedAt?->format('Y-m-d H:i') ?? __('keywords.not_specified_arabic') }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ __('keywords.changed_by') }}:
                                        <span class="font-medium text-gray-900">
                                            {{ $maintenance->user?->name ?? __('keywords.not_specified_arabic') }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ __('keywords.technician_name') }}:
                                        <span
                                            class="font-medium text-gray-900">{{ $maintenance->technician_name }}</span>
                                    </div>
                                    <div
                                        class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">
                                        {{ __('keywords.maintenance_cost') }}:
                                        {{ number_format((float) $maintenance->cost, 2) }}
                                        {{ __('keywords.currency') }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.changed_candles') }}
                                        </p>
                                        @if ($changedCandles->isEmpty())
                                            <p class="text-sm text-gray-500">{{ __('keywords.no_data_available') }}</p>
                                        @else
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($changedCandles as $candleName)
                                                    <span
                                                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">
                                                        {{ $candleName }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {{ __('keywords.maintenance_products') }}
                                        </p>
                                        @if ($installedProducts->isEmpty())
                                            <p class="text-sm text-gray-500">
                                                {{ __('keywords.no_maintenance_products') }}</p>
                                        @else
                                            <div class="space-y-1.5">
                                                @foreach ($installedProducts as $productName => $quantity)
                                                    <div class="flex items-center justify-between text-sm">
                                                        <span
                                                            class="font-medium text-gray-800">{{ $productName }}</span>
                                                        <span class="text-gray-600">x{{ $quantity }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if ($maintenance->description)
                                    <div
                                        class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                        <span class="font-medium">{{ __('keywords.description') }}:</span>
                                        {{ $maintenance->description }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @can('manage_water_filters')
        {{-- Add Reading Modal --}}
        <x-modal name="add-reading" title="{{ __('keywords.add_reading') }}" maxWidth="lg">
            <x-slot:body>
                <div class="space-y-5">
                    <x-input name="readingForm.technician_name" label="{{ __('keywords.technician_name') }}"
                        placeholder="{{ __('keywords.enter_technician_name') }}"
                        wire:model.blur="readingForm.technician_name" required />

                    <x-input type="number" step="0.01" name="readingForm.tds" label="{{ __('keywords.tds') }}"
                        placeholder="0" wire:model.blur="readingForm.tds" required />

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700">
                            {{ __('keywords.water_quality') }} <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.blur="readingForm.water_quality"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 px-3 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">{{ __('keywords.select_water_quality') }}</option>
                            @foreach ($waterQualityOptions as $quality)
                                <option value="{{ $quality->value }}">{{ __('keywords.' . $quality->label()) }}</option>
                            @endforeach
                        </select>
                        @error('readingForm.water_quality')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 cursor-pointer">
                        <input type="checkbox" wire:model.live="readingForm.before_installment"
                            class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span>{{ __('keywords.before_installment') }}</span>
                    </label>
                    @error('readingForm.before_installment')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    @if ($canManageCreatedAt)
                        <x-input type="datetime-local" name="readingForm.created_at"
                            label="{{ __('keywords.created_at') }}" wire:model.live="readingForm.created_at" />
                    @endif

                    @if (!$filter->is_installed && $readingForm['before_installment'])
                        <div class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs text-blue-700">
                            <i class="fas fa-info-circle me-1"></i>
                            {{ __('keywords.before_installment_hint') }}
                        </div>
                    @endif
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-add-reading')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="createReading">{{ __('keywords.add') }}</x-button>
            </x-slot:footer>
        </x-modal>

        {{-- Record Maintenance Modal --}}
        <x-modal name="mark-candle" title="{{ __('keywords.record_maintenance') }}" maxWidth="2xl">
            <x-slot:body>
                <div class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            {{ __('keywords.changed_candles') }} <span class="text-red-500">*</span>
                        </label>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($candles as $candle)
                                <label @class([
                                    'flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-colors cursor-pointer',
                                    'border-emerald-300 bg-emerald-50 text-emerald-700' => in_array(
                                        $candle['key'],
                                        $maintenanceForm['selected_candles'] ?? [],
                                        true),
                                    'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' => !in_array(
                                        $candle['key'],
                                        $maintenanceForm['selected_candles'] ?? [],
                                        true),
                                ])>
                                    <input type="checkbox" value="{{ $candle['key'] }}"
                                        wire:model.live="maintenanceForm.selected_candles"
                                        class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    <span>{{ $candle['name'] }}</span>
                                </label>
                            @endforeach
                        </div>

                        @error('maintenanceForm.selected_candles')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        @error('maintenanceForm.selected_candles.*')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-input name="maintenanceForm.technician_name" label="{{ __('keywords.technician_name') }}"
                            placeholder="{{ __('keywords.enter_technician_name') }}"
                            wire:model.blur="maintenanceForm.technician_name" required />

                        <x-input type="datetime-local" name="maintenanceForm.replaced_at"
                            label="{{ __('keywords.replaced_at') }}" wire:model.blur="maintenanceForm.replaced_at"
                            :disabled="!$canManageCreatedAt" required />
                    </div>

                    @unless ($canManageCreatedAt)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600">
                            {{ __('keywords.replaced_at_permission_hint') }}
                        </div>
                    @endunless

                    @error('maintenanceForm.technician_name')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @error('maintenanceForm.replaced_at')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="grid grid-cols-1 gap-4">
                        <x-input type="number" step="0.01" min="0" name="maintenanceForm.cost"
                            label="{{ __('keywords.maintenance_cost') }}" placeholder="0"
                            wire:model.blur="maintenanceForm.cost" required />

                        <x-textarea name="maintenanceForm.description" label="{{ __('keywords.description') }}"
                            placeholder="{{ __('keywords.enter_description') }}"
                            wire:model.blur="maintenanceForm.description" rows="3" />
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900">{{ __('keywords.maintenance_products') }}</h4>
                            <span
                                class="text-xs text-gray-500">{{ __('keywords.only_purchased_maintenance_products') }}</span>
                        </div>

                        @if (empty($maintenanceProducts))
                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-500">
                                {{ __('keywords.no_maintenance_products_available') }}
                            </div>
                        @else
                            <div class="space-y-2">
                                @foreach ($maintenanceProducts as $product)
                                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-3">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="space-y-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $product['product_name'] }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ __('keywords.available_quantity') }}:
                                                    {{ $product['available_quantity'] }}
                                                </p>
                                            </div>

                                            <div class="w-full sm:w-28">
                                                <x-input type="number" min="0" step="1"
                                                    max="{{ $product['available_quantity'] }}"
                                                    name="maintenanceForm.items.{{ $product['product_id'] }}"
                                                    placeholder="0"
                                                    wire:model.blur="maintenanceForm.items.{{ $product['product_id'] }}" />
                                            </div>
                                        </div>

                                        @error('maintenanceForm.items.' . $product['product_id'])
                                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </x-slot:body>
            <x-slot:footer>
                <x-button variant="secondary"
                    @click="$dispatch('close-modal-mark-candle')">{{ __('keywords.cancel') }}</x-button>
                <x-button variant="primary" wire:click="saveMaintenance" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveMaintenance">{{ __('keywords.save') }}</span>
                    <span wire:loading wire:target="saveMaintenance">{{ __('keywords.loading') }}</span>
                </x-button>
            </x-slot:footer>
        </x-modal>
    @endcan
</div>
