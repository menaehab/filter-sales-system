<div x-on:confirmed-mark-candle.window="$wire.markCandleReplaced()">
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
                    <span class="font-medium text-gray-900">{{ $filter->customer?->phone ?? '—' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">{{ __('keywords.installed_at') }}</span>
                    <span
                        class="font-medium text-gray-900">{{ $filter->installed_at?->format('Y-m-d') ?? __('keywords.not_installed') }}</span>
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
                            title="{{ __('keywords.click_to_mark_replaced') }}">
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
                                        {{ $candle['replaced_at']->format('Y-m-d') }}</div>
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
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('keywords.candle_changes_history') }}</h3>
                </div>

                @if ($candleChanges->isEmpty())
                    <div class="px-5 py-8 text-center text-sm text-gray-500">
                        {{ __('keywords.no_candle_changes_history') }}
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        {{ __('keywords.candle') }}
                                    </th>
                                    <th
                                        class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        {{ __('keywords.changed_by') }}
                                    </th>
                                    <th
                                        class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        {{ __('keywords.changed_at') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($candleChanges as $change)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ $change->candle_name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $change->user?->name ?? __('keywords.not_specified_arabic') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $change->replaced_at?->format('Y-m-d H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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

                    @if (!$filter->installed_at && $readingForm['before_installment'])
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

        {{-- Mark Candle Replaced Confirmation Modal --}}
        <x-confirm-modal name="mark-candle" title="{{ __('keywords.mark_candle_replaced') }}"
            message="{{ __('keywords.mark_candle_replaced_confirmation') }}" confirmText="{{ __('keywords.confirm') }}"
            variant="primary" />
    @endcan
</div>
