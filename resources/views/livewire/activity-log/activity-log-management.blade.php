<div>
    {{-- Page Header --}}
    <x-page-header>
        <x-slot:title>{{ __('keywords.activity_log_management') }}</x-slot:title>
        <x-slot:description>{{ __('keywords.activity_log') }}</x-slot:description>
    </x-page-header>

    {{-- Filters --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
            {{-- Date From --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('keywords.from_date') }}</label>
                <x-input type="date" name="dateFrom" wire:model.live="dateFrom" class="w-full" />
            </div>

            {{-- Date To --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('keywords.to_date') }}</label>
                <x-input type="date" name="dateTo" wire:model.live="dateTo" class="w-full" />
            </div>

            {{-- Activity Type --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('keywords.activity_type') }}</label>
                <x-select name="activityType" wire:model.live="activityType" class="w-full">
                    <option value="">{{ __('keywords.select_activity_type') }}</option>
                    @foreach ($this->availableEvents as $event)
                        <option value="{{ $event }}">{{ $this->translateEventType($event) }}</option>
                    @endforeach
                </x-select>
            </div>

            {{-- Model Type --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('keywords.model_type') }}</label>
                <x-select name="modelType" wire:model.live="modelType" class="w-full">
                    <option value="">{{ __('keywords.select_model') }}</option>
                    @foreach ($this->availableModels as $model)
                        <option value="{{ $model['value'] }}">{{ $model['label'] }}</option>
                    @endforeach
                </x-select>
            </div>

            {{-- User --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('keywords.causer') }}</label>
                <x-select name="causerId" wire:model.live="causerId" class="w-full">
                    <option value="">{{ __('keywords.select_user') }}</option>
                    <option value="system">{{ __('keywords.system') }}</option>
                    @foreach ($this->availableUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-select>
            </div>
        </div>

        {{-- Clear Filters --}}
        @if ($dateFrom || $dateTo || $activityType || $modelType || $causerId)
            <div class="mt-4">
                <x-button
                    wire:click="$set('dateFrom', null); $set('dateTo', null); $set('activityType', null); $set('modelType', null); $set('causerId', null)"
                    color="secondary" size="sm">
                    <i class="fas fa-times"></i>
                    {{ __('keywords.clear') }}
                </x-button>
            </div>
        @endif
    </div>

    {{-- Search & Pagination --}}
    <x-search-toolbar />

    {{-- Activity Log Table --}}
    <x-data-table :searchable="false" :paginated="false" :headers="[
        ['label' => __('keywords.activity_type'), 'key' => 'event'],
        ['label' => __('keywords.model_type'), 'key' => 'subject_type'],
        ['label' => __('keywords.performed_by'), 'key' => 'causer'],
        ['label' => __('keywords.date'), 'key' => 'created_at'],
        ['label' => __('keywords.actions'), 'key' => 'actions', 'sortable' => false],
    ]">


        @forelse($this->activities as $activity)

            <tr class="transition-colors hover:bg-gray-50" wire:key="activity-{{ $activity->id }}">
                {{-- Activity Type --}}
                <td class="whitespace-nowrap px-5 py-4">
                    @php
                        $badgeColor = match ($activity->event) {
                            'created' => 'emerald',
                            'updated' => 'blue',
                            'deleted' => 'rose',
                            'activity_read_notification', 'activity_read_all_notifications' => 'blue',
                            'activity_delete_notification',
                            'activity_delete_all_read_notifications',
                            'activity_delete_all_notifications'
                                => 'rose',
                            'activity_send_customer_installment_reminder',
                            'activity_send_supplier_installment_reminder',
                            'activity_send_filter_candle_reminder',
                            'activity_send_low_stock_alert'
                                => 'amber',
                            'activity_mark_filter_candle_replaced' => 'teal',
                            default => 'gray',
                        };
                    @endphp
                    <x-badge :label="$this->translateEventType($activity->event)" :color="$badgeColor" />
                </td>

                {{-- Model Type --}}
                <td class="px-5 py-4">
                    <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700">
                        {{ $this->translateModelType($activity->subject_type) }}
                    </span>
                </td>
                {{-- Performed By --}}
                <td class="px-5 py-4">
                    <span class="text-sm text-gray-700">{{ $activity->causer?->name ?? __('keywords.system') }}</span>
                </td>

                {{-- Date --}}
                <td class="whitespace-nowrap px-5 py-4">
                    <span class="text-sm text-gray-500">{{ $activity->created_at->format('Y-m-d H:i') }}</span>
                </td>

                {{-- Actions --}}
                <td class="whitespace-nowrap px-5 py-4">
                    @if ($activity->properties->isNotEmpty())
                        <button wire:click="toggleRow({{ $activity->id }})"
                            class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                            <i
                                class="fas {{ $this->isRowExpanded($activity->id) ? 'fa-chevron-up' : 'fa-chevron-down' }}"></i>
                            {{ $this->isRowExpanded($activity->id) ? __('keywords.hide_changes') : __('keywords.show_changes') }}
                        </button>
                    @endif
                </td>
            </tr>

            {{-- Expandable Row for Changes --}}
            @if ($this->isRowExpanded($activity->id))
                <tr wire:key="activity-expanded-{{ $activity->id }}">
                    <td colspan="6" class="bg-gray-50 px-5 py-4">
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            {{-- Old Values --}}
                            @if ($activity->event === 'updated' && isset($activity->properties['old']) && is_array($activity->properties['old']))
                                <div>
                                    <h4 class="mb-2 text-sm font-semibold text-gray-900">
                                        {{ __('keywords.old_values') }}</h4>
                                    <div class="rounded-lg border border-rose-200 bg-rose-50/70 p-3">
                                        <table class="w-full text-sm text-start text-gray-700">
                                            <thead>
                                                <tr class="border-b border-rose-200 text-xs text-gray-600">
                                                    <th class="pb-2 pe-3 font-semibold">{{ __('keywords.field') }}</th>
                                                    <th class="pb-2 font-semibold">{{ __('keywords.value') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($activity->properties['old'] as $key => $value)
                                                    @if (!($key == 'slug') && !($key == 'updated_at') && !($key == 'id') && !\Illuminate\Support\Str::endsWith($key, '_id'))
                                                        <tr class="border-b border-rose-100 align-top last:border-b-0">
                                                            <td class="py-2 pe-3 font-medium text-gray-700">
                                                                {{ __('keywords.' . $key) }}</td>
                                                            <td class="py-2 wrap-break-word text-gray-700">
                                                                {{ $this->formatAttributeValue($value) }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- New Values --}}
                            @if (isset($activity->properties['attributes']) && is_array($activity->properties['attributes']))
                                <div>
                                    <h4 class="mb-2 text-sm font-semibold text-gray-900">
                                        {{ __('keywords.new_values') }}</h4>
                                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/70 p-3">
                                        <table class="w-full text-sm text-start text-gray-700">
                                            <thead>
                                                <tr class="border-b border-emerald-200 text-xs text-gray-600">
                                                    <th class="pb-2 pe-3 font-semibold">{{ __('keywords.field') }}</th>
                                                    <th class="pb-2 font-semibold">{{ __('keywords.value') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($activity->properties['attributes'] as $key => $value)
                                                    @if (!($key == 'slug') && !($key == 'updated_at') && !($key == 'id') && !\Illuminate\Support\Str::endsWith($key, '_id'))
                                                        <tr
                                                            class="border-b border-emerald-100 align-top last:border-b-0">
                                                            <td class="py-2 pe-3 font-medium text-gray-700">
                                                                {{ __('keywords.' . $key) }}</td>
                                                            <td class="py-2 wrap-break-word text-gray-700">
                                                                {{ $this->formatAttributeValue($value) }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- For Created/Deleted Events --}}
                            @if (
                                ($activity->event === 'created' || $activity->event === 'deleted') &&
                                    !empty($activity->properties) &&
                                    is_array($activity->properties))
                                <div class="lg:col-span-2">
                                    <h4 class="mb-2 text-sm font-semibold text-gray-900">
                                        {{ $activity->event === 'created' ? __('keywords.data') : __('keywords.old_values') }}
                                    </h4>
                                    <div
                                        class="rounded-lg border {{ $activity->event === 'created' ? 'border-emerald-200 bg-emerald-50/70' : 'border-rose-200 bg-rose-50/70' }} p-3">
                                        <table class="w-full text-sm text-start text-gray-700">
                                            <thead>
                                                <tr
                                                    class="border-b {{ $activity->event === 'created' ? 'border-emerald-200' : 'border-rose-200' }} text-xs text-gray-600">
                                                    <th class="pb-2 pe-3 font-semibold">{{ __('keywords.field') }}</th>
                                                    <th class="pb-2 font-semibold">{{ __('keywords.value') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($activity->properties as $key => $value)
                                                    @if (!($key == 'slug') && !($key == 'updated_at'))
                                                        <tr
                                                            class="border-b {{ $activity->event === 'created' ? 'border-emerald-100' : 'border-rose-100' }} align-top last:border-b-0">
                                                            <td class="py-2 pe-3 font-medium text-gray-700">
                                                                {{ $this->translateAttributeLabel($key) }}</td>
                                                            <td class="py-2 wrap-break-word text-gray-700">
                                                                {{ $this->formatAttributeValue($value) }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- No Changes --}}
                            @if ($activity->properties->isEmpty())
                                <div class="lg:col-span-2">
                                    <p class="text-sm text-gray-500">{{ __('keywords.no_changes') }}</p>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endif
        @empty
            <x-empty-state :message="__('keywords.no_activities_found')" />
        @endforelse
    </x-data-table>

    {{-- Pagination --}}
    <x-pagination-info :paginator="$this->activities" />
</div>
