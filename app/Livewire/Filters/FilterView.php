<?php

namespace App\Livewire\Filters;

use App\Enums\WaterQualityTypeEnum;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Maintenance;
use App\Models\MaintenanceItem;
use App\Models\SaleItem;
use App\Models\WaterFilter;
use App\Models\WaterReading;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class FilterView extends Component
{
    use WithSearchAndPagination;

    public WaterFilter $filter;

    public string $activeTab = 'overview';

    public array $readingForm = [
        'technician_name' => '',
        'tds' => '',
        'water_quality' => '',
        'before_installment' => false,
        'created_at' => '',
    ];

    public array $maintenanceForm = [
        'selected_candles' => [],
        'technician_name' => '',
        'replaced_at' => '',
        'cost' => '',
        'description' => '',
        'items' => [],
    ];

    public function mount(WaterFilter $filter): void
    {
        $this->filter = $filter->load('customer');
        $this->resetReadingForm();
        $this->resetMaintenanceForm();
    }

    public function getReadingsProperty()
    {
        return $this->filter->readings()
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function getCandlesProperty(): array
    {
        $status = $this->filter->candle_status;

        return [
            [
                'key' => 'candle_1',
                'name' => __('keywords.candle_1'),
                'status' => $status['candle_1'],
                'next_date' => $this->filter->candle_1_next_date,
                'replaced_at' => $this->filter->candle_1_replaced_at,
                'interval' => $this->filter->candle_1_interval_months.' '.__('keywords.months'),
            ],
            [
                'key' => 'candle_2_3',
                'name' => __('keywords.candle_2_3'),
                'status' => $status['candle_2_3'],
                'next_date' => $this->filter->candle_2_3_next_date,
                'replaced_at' => $this->filter->candle_2_3_replaced_at,
                'interval' => '5 '.__('keywords.months'),
            ],
            [
                'key' => 'candle_4',
                'name' => __('keywords.candle_4'),
                'status' => $status['candle_4'],
                'next_date' => null,
                'replaced_at' => $this->filter->candle_4_replaced_at,
                'interval' => 'TDS >= 100',
            ],
            [
                'key' => 'candle_5',
                'name' => __('keywords.candle_5'),
                'status' => $status['candle_5'],
                'next_date' => $this->filter->candle_5_next_date,
                'replaced_at' => $this->filter->candle_5_replaced_at,
                'interval' => '6-8 '.__('keywords.months'),
            ],
            [
                'key' => 'candle_6',
                'name' => __('keywords.candle_6'),
                'status' => $status['candle_6'],
                'next_date' => $this->filter->candle_6_next_date,
                'replaced_at' => $this->filter->candle_6_replaced_at,
                'interval' => '8-10 '.__('keywords.months'),
            ],
            [
                'key' => 'candle_7',
                'name' => __('keywords.candle_7'),
                'status' => $status['candle_7'],
                'next_date' => $this->filter->candle_7_next_date,
                'replaced_at' => $this->filter->candle_7_replaced_at,
                'interval' => '10-12 '.__('keywords.months'),
            ],
        ];
    }

    public function getMaintenancesProperty()
    {
        return $this->filter->maintenances()
            ->with([
                'user',
                'items.saleItem.product',
                'candleChanges',
            ])
            ->latest('created_at')
            ->limit(20)
            ->get();
    }

    public function getMaintenanceProductsProperty(): array
    {
        return collect($this->getMaintenanceProductsStock())
            ->map(fn (array $productStock) => [
                'product_id' => $productStock['product_id'],
                'product_name' => $productStock['product_name'],
                'total_purchased' => $productStock['total_purchased'],
                'total_used' => $productStock['total_used'],
                'available_quantity' => $productStock['available_quantity'],
            ])
            ->values()
            ->toArray();
    }

    public function getCanManageCreatedAtProperty(): bool
    {
        return (bool) auth()->user()?->can('manage_created_at');
    }

    public function getServiceVisitsProperty()
    {
        return $this->filter->serviceVisits()
            ->with('user')
            ->latest('created_at')
            ->get();
    }

    public function setActiveTab(string $tab): void
    {
        if (! in_array($tab, ['overview', 'service-visits'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function markServiceVisitCompleted(int $visitId): void
    {
        $this->authorize('manage_service_visits');

        $visit = $this->filter->serviceVisits()->findOrFail($visitId);

        if (! $visit->is_completed) {
            $visit->update(['is_completed' => true]);
        }
    }

    public function openAddReading(): void
    {
        $this->resetReadingForm();
        $this->dispatch('open-modal-add-reading');
    }

    public function createReading(): void
    {
        $this->authorize('manage_water_filters');

        $this->validate([
            'readingForm.technician_name' => 'required|string|max:255',
            'readingForm.tds' => 'required|numeric|min:0',
            'readingForm.water_quality' => 'required|in:'.implode(',', WaterQualityTypeEnum::values()),
            'readingForm.before_installment' => 'boolean',
            'readingForm.created_at' => 'nullable|date',
        ], [], [
            'readingForm.technician_name' => __('keywords.technician_name'),
            'readingForm.tds' => __('keywords.tds'),
            'readingForm.water_quality' => __('keywords.water_quality'),
            'readingForm.before_installment' => __('keywords.before_installment'),
            'readingForm.created_at' => __('keywords.created_at'),
        ]);

        // Check if trying to add before_installment reading when one already exists
        if ($this->readingForm['before_installment']) {
            $existingPreReading = $this->filter->readings()->where('before_installment', true)->exists();
            if ($existingPreReading) {
                $this->addError('readingForm.before_installment', __('keywords.before_installment_already_exists'));

                return;
            }
        }

        WaterReading::create([
            'technician_name' => $this->readingForm['technician_name'],
            'tds' => $this->readingForm['tds'],
            'water_quality' => $this->readingForm['water_quality'],
            'before_installment' => $this->readingForm['before_installment'],
            'water_filter_id' => $this->filter->id,
            'created_at' => $this->resolveReadingCreatedAt(),
        ]);

        $this->filter->refresh();
        $this->resetReadingForm();
        $this->dispatch('close-modal-add-reading');
        $this->resetPage();
    }

    public function openMarkCandle(string $candleKey): void
    {
        $validCandleKeys = collect($this->candles)->pluck('key')->all();
        $selectedCandles = in_array($candleKey, $validCandleKeys, true) ? [$candleKey] : [];

        $this->resetMaintenanceForm($selectedCandles);
        $this->dispatch('open-modal-mark-candle');
    }

    public function markCandleReplaced(): void
    {
        $this->saveMaintenance();
    }

    public function saveMaintenance(): void
    {
        $this->authorize('manage_water_filters');

        $availableCandleKeys = collect($this->candles)->pluck('key')->all();
        $productStock = collect($this->getMaintenanceProductsStock())
            ->keyBy(fn (array $item) => (string) $item['product_id']);

        $validator = Validator::make(
            ['maintenanceForm' => $this->maintenanceForm],
            [
                'maintenanceForm.selected_candles' => ['required', 'array', 'min:1'],
                'maintenanceForm.selected_candles.*' => ['required', 'string', Rule::in($availableCandleKeys)],
                'maintenanceForm.technician_name' => ['required', 'string', 'max:255'],
                'maintenanceForm.replaced_at' => ['required', 'date'],
                'maintenanceForm.cost' => ['required', 'numeric', 'min:0'],
                'maintenanceForm.description' => ['nullable', 'string', 'max:1000'],
                'maintenanceForm.items' => ['array'],
                'maintenanceForm.items.*' => ['nullable', 'integer', 'min:0'],
            ],
            [],
            [
                'maintenanceForm.selected_candles' => __('keywords.changed_candles'),
                'maintenanceForm.selected_candles.*' => __('keywords.candle'),
                'maintenanceForm.technician_name' => __('keywords.technician_name'),
                'maintenanceForm.replaced_at' => __('keywords.replaced_at'),
                'maintenanceForm.cost' => __('keywords.maintenance_cost'),
                'maintenanceForm.description' => __('keywords.description'),
                'maintenanceForm.items.*' => __('keywords.quantity'),
            ]
        );

        $validator->after(function ($validator) use ($productStock) {
            $requestedItems = collect($this->maintenanceForm['items'] ?? [])
                ->mapWithKeys(fn ($quantity, $productId) => [(string) $productId => (int) $quantity])
                ->filter(fn (int $quantity) => $quantity > 0);

            foreach ($requestedItems as $productId => $quantity) {
                $stock = $productStock->get((string) $productId);

                if (! $stock) {
                    $validator->errors()->add('maintenanceForm.items.'.$productId, __('keywords.maintenance_product_unavailable'));

                    continue;
                }

                if ($quantity > (int) $stock['available_quantity']) {
                    $validator->errors()->add('maintenanceForm.items.'.$productId, __('keywords.maintenance_quantity_exceeded'));
                }
            }
        });

        if ($validator->fails()) {
            $this->setErrorBag($validator->getMessageBag());

            return;
        }

        $validated = $validator->validated()['maintenanceForm'];
        $requestedItems = collect($validated['items'] ?? [])
            ->mapWithKeys(fn ($quantity, $productId) => [(string) $productId => (int) $quantity])
            ->filter(fn (int $quantity) => $quantity > 0);

        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $replacedAt = $this->canManageCreatedAt
            ? Carbon::parse($validated['replaced_at'])
            : now();

        DB::transaction(function () use ($validated, $requestedItems, $productStock, $user, $replacedAt) {
            $maintenance = Maintenance::create([
                'cost' => $validated['cost'],
                'technician_name' => $validated['technician_name'],
                'description' => blank($validated['description'] ?? null) ? null : $validated['description'],
                'user_id' => $user->id,
                'water_filter_id' => $this->filter->id,
            ]);

            foreach ($requestedItems as $productId => $requestedQuantity) {
                $stock = $productStock->get((string) $productId);
                $remainingQuantity = $requestedQuantity;

                foreach ($stock['sale_items'] as $saleItemStock) {
                    if ($remainingQuantity <= 0) {
                        break;
                    }

                    $allocatableQuantity = min($remainingQuantity, (int) $saleItemStock['available_quantity']);

                    if ($allocatableQuantity <= 0) {
                        continue;
                    }

                    MaintenanceItem::create([
                        'maintenance_id' => $maintenance->id,
                        'sale_item_id' => (int) $saleItemStock['sale_item_id'],
                        'quantity' => $allocatableQuantity,
                    ]);

                    $remainingQuantity -= $allocatableQuantity;
                }

                if ($remainingQuantity > 0) {
                    throw ValidationException::withMessages([
                        'maintenanceForm.items.'.$productId => __('keywords.maintenance_quantity_exceeded'),
                    ]);
                }
            }

            $this->filter->markCandlesReplaced($validated['selected_candles'], $user, $maintenance, $replacedAt);
        });

        $this->filter->refresh();
        $this->resetMaintenanceForm();
        $this->dispatch('close-modal-mark-candle');
    }

    protected function getMaintenanceProductsStock(): array
    {
        if (! $this->filter->customer_id) {
            return [];
        }

        $saleItems = SaleItem::query()
            ->with('product:id,name')
            ->whereNotNull('product_id')
            ->whereHas('sale', fn ($query) => $query->where('customer_id', $this->filter->customer_id))
            ->whereHas('product', fn ($query) => $query->where('for_maintenance', true))
            ->orderBy('created_at')
            ->get(['id', 'product_id', 'quantity']);

        if ($saleItems->isEmpty()) {
            return [];
        }

        $usedBySaleItem = MaintenanceItem::query()
            ->selectRaw('maintenance_items.sale_item_id, SUM(maintenance_items.quantity) as used_quantity')
            ->join('maintenances', 'maintenance_items.maintenance_id', '=', 'maintenances.id')
            ->join('water_filters', 'maintenances.water_filter_id', '=', 'water_filters.id')
            ->where('water_filters.customer_id', $this->filter->customer_id)
            ->groupBy('maintenance_items.sale_item_id')
            ->pluck('used_quantity', 'maintenance_items.sale_item_id');

        $products = [];

        foreach ($saleItems as $saleItem) {
            if (! $saleItem->product) {
                continue;
            }

            $purchasedQuantity = (int) floor((float) $saleItem->quantity);
            $usedQuantity = (int) floor((float) ($usedBySaleItem->get($saleItem->id, 0) ?? 0));
            $availableQuantity = max(0, $purchasedQuantity - $usedQuantity);

            if ($availableQuantity <= 0) {
                continue;
            }

            $productId = (int) $saleItem->product_id;

            if (! isset($products[$productId])) {
                $products[$productId] = [
                    'product_id' => $productId,
                    'product_name' => $saleItem->product->name,
                    'total_purchased' => 0,
                    'total_used' => 0,
                    'available_quantity' => 0,
                    'sale_items' => [],
                ];
            }

            $products[$productId]['total_purchased'] += $purchasedQuantity;
            $products[$productId]['total_used'] += min($usedQuantity, $purchasedQuantity);
            $products[$productId]['available_quantity'] += $availableQuantity;
            $products[$productId]['sale_items'][] = [
                'sale_item_id' => (int) $saleItem->id,
                'available_quantity' => $availableQuantity,
            ];
        }

        uasort($products, fn (array $first, array $second) => strcasecmp($first['product_name'], $second['product_name']));

        return array_values($products);
    }

    protected function resetReadingForm(): void
    {
        $this->readingForm = [
            'technician_name' => '',
            'tds' => '',
            'water_quality' => '',
            'before_installment' => false,
            'created_at' => now()->format('Y-m-d\TH:i'),
        ];
    }

    protected function resolveReadingCreatedAt(): CarbonInterface
    {
        $createdAt = $this->readingForm['created_at'] ?? null;

        if ($this->canManageCreatedAt && filled($createdAt)) {
            return Carbon::parse($createdAt);
        }

        return now();
    }

    protected function resetMaintenanceForm(array $selectedCandles = []): void
    {
        $this->maintenanceForm = [
            'selected_candles' => $selectedCandles,
            'technician_name' => '',
            'replaced_at' => now()->format('Y-m-d\TH:i'),
            'cost' => '',
            'description' => '',
            'items' => [],
        ];
    }

    public function render()
    {
        return view('livewire.filters.filter-view', [
            'readings' => $this->readings,
            'candles' => $this->candles,
            'maintenances' => $this->maintenances,
            'serviceVisits' => $this->serviceVisits,
            'maintenanceProducts' => $this->maintenanceProducts,
            'canManageCreatedAt' => $this->canManageCreatedAt,
            'waterQualityOptions' => WaterQualityTypeEnum::cases(),
        ]);
    }
}
