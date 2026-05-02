<?php

namespace App\Livewire\Filters;

use App\Actions\WaterFilters\CreateWaterFilterAction;
use App\Actions\WaterFilters\DeleteWaterFilterAction;
use App\Actions\WaterFilters\UpdateWaterFilterAction;
use App\Livewire\Traits\HasCrudModals;
use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\HasForm;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\Customer;
use App\Models\Place;
use App\Models\ServiceVisit;
use App\Models\WaterFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class FilterManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public $customerSlug = '';

    public $placeId = '';

    public $customerSearch = '';

    public $customerModalSearch = '';

    public array $candleNeedsReplacement = [];

    public ?int $serviceVisitFilterId = null;

    public array $serviceVisitDueCandles = [];

    public array $serviceVisitForm = [
        'maintenance_input' => '',
        'technician_name' => '',
        'cost' => '',
        'notes' => '',
    ];

    public function mount(): void
    {
        $this->resetForm();
        $this->resetServiceVisitForm();

        if ($this->customerSlug) {
            $customer = $this->customers->firstWhere('slug', $this->customerSlug);
            $this->customerSearch = $customer
                ? trim($customer->name.' ('.($customer->code ?? '—').')')
                : '';
        }
    }

    protected function getDefaultForm(): array
    {
        return [
            'filter_model' => '',
            'address' => '',
            'is_installed' => false,
            'installed_at' => null,
            'customer_id' => null,
        ];
    }

    public function updatedFormIsInstalled($value): void
    {
        if (! ((bool) $value)) {
            $this->form['installed_at'] = null;
        }
    }

    protected function getModelClass(): string
    {
        return WaterFilter::class;
    }

    protected function getSearchableFields(): array
    {
        return ['filter_model', 'address', 'customer.name', 'customer.code', 'customer.phones.number'];
    }

    protected function getWithRelations(): array
    {
        return ['customer', 'customer.phones'];
    }

    protected function additionalQueryString(): array
    {
        return [
            'customerSlug' => ['as' => 'customer', 'except' => ''],
            'placeId' => ['as' => 'place', 'except' => ''],
        ];
    }

    public function updatingPlaceId(): void
    {
        $this->resetPage();
    }

    public function updatingCustomerSlug(): void
    {
        $this->resetPage();
        $customer = $this->customers->firstWhere('slug', $this->customerSlug);
        $this->customerSearch = $customer
            ? trim($customer->name.' ('.($customer->code ?? '—').')')
            : '';
    }

    public function updatingCandleNeedsReplacement(): void
    {
        $this->resetPage();
    }

    protected function applyAdditionalFilters(Builder $query): void
    {
        if ($this->customerSlug) {
            $query->whereHas('customer', fn ($q) => $q->where('slug', $this->customerSlug));
        }

        if ($this->placeId) {
            $query->whereHas('customer', fn ($q) => $q->where('place_id', $this->placeId));
        }

        $selectedCandles = $this->selectedCandleFilters();

        if ($selectedCandles === []) {
            return;
        }

        $query->where(function (Builder $builder) use ($selectedCandles) {
            foreach ($selectedCandles as $index => $candleKey) {
                $method = $index === 0 ? 'where' : 'orWhere';

                $builder->{$method}(function (Builder $candleQuery) use ($candleKey) {
                    $this->applyCandleNeedsReplacementFilter($candleQuery, $candleKey);
                });
            }
        });
    }

    public function clearCandleNeedsReplacement(): void
    {
        $this->candleNeedsReplacement = [];
        $this->resetPage();
    }

    public function create(CreateWaterFilterAction $action): void
    {
        $this->authorizeManageFilters();

        $request = new \App\Http\Requests\WaterFilters\CreateWaterFilterRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $action->execute($validated['form']);
        $this->resetForm();
        $this->customerModalSearch = '';
        $this->dispatch('close-modal-create-filter');
        $this->resetPage();
    }

    public function updateFilter(UpdateWaterFilterAction $action): void
    {
        $this->authorizeManageFilters();

        $request = new \App\Http\Requests\WaterFilters\UpdateWaterFilterRequest;
        $rules = collect($request->rules())->mapWithKeys(fn ($rule, $key) => ["form.{$key}" => $rule])->toArray();
        $rules['form.customer_id'] = ['required', 'exists:customers,id', Rule::unique('water_filters', 'customer_id')->ignore($this->editId)];
        $rules['form.filter_model'] = ['required', 'string', 'max:255', Rule::unique('water_filters', 'filter_model')->ignore($this->editId)];
        $attributes = collect($request->attributes())->mapWithKeys(fn ($attr, $key) => ["form.{$key}" => $attr])->toArray();
        $validated = $this->validate($rules, $request->messages(), $attributes);

        $filter = WaterFilter::findOrFail($this->editId);
        $action->execute($filter, $validated['form']);
        $this->resetForm();
        $this->editId = null;
        $this->customerModalSearch = '';
        $this->dispatch('close-modal-edit-filter');
        $this->resetPage();
    }

    public function setDelete($id): void
    {
        $this->authorizeManageFilters();

        $this->openDeleteModal($id, 'open-modal-delete-filter');
    }

    public function openEdit($id): void
    {
        $this->authorizeManageFilters();

        $filter = WaterFilter::with('customer')->findOrFail($id);

        $this->openEditModal($filter->id, 'open-modal-edit-filter');

        $this->form = [
            'filter_model' => $filter->filter_model,
            'address' => $filter->address,
            'is_installed' => (bool) $filter->is_installed,
            'installed_at' => $filter->installed_at
                ? \Illuminate\Support\Carbon::parse($filter->installed_at)->format('Y/m/d')
                : null,
            'customer_id' => $filter->customer_id,
        ];

        $this->customerModalSearch = $filter->customer?->name ?? '';
    }

    public function delete(DeleteWaterFilterAction $action): void
    {
        $this->authorizeManageFilters();

        $filter = WaterFilter::find($this->deleteId);
        if ($filter) {
            $action->execute($filter);
        }
        $this->deleteId = null;
        $this->dispatch('close-modal-delete-filter');
        $this->resetPage();
    }

    public function openCreateServiceVisit(int $filterId): void
    {
        $this->authorizeManageServiceVisits();

        $filter = WaterFilter::query()->with('customer')->findOrFail($filterId);

        $this->serviceVisitFilterId = $filter->id;
        $this->serviceVisitDueCandles = $this->dueCandleLabels($filter);
        $this->serviceVisitForm = [
            'maintenance_input' => '',
            'technician_name' => '',
            'cost' => '',
            'notes' => '',
        ];

        $this->dispatch('open-modal-create-service-visit');
    }

    public function createServiceVisit(): void
    {
        $this->authorizeManageServiceVisits();

        $validated = $this->validate([
            'serviceVisitForm.maintenance_input' => ['nullable', 'string', 'max:255'],
            'serviceVisitForm.technician_name' => ['nullable', 'string', 'max:255'],
            'serviceVisitForm.cost' => ['nullable', 'numeric', 'min:0'],
            'serviceVisitForm.notes' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'serviceVisitForm.maintenance_input' => __('keywords.maintenance_type'),
            'serviceVisitForm.technician_name' => __('keywords.technician_name'),
            'serviceVisitForm.cost' => __('keywords.maintenance_cost'),
            'serviceVisitForm.notes' => __('keywords.notes'),
        ]);

        $filter = WaterFilter::query()->with('customer')->findOrFail($this->serviceVisitFilterId);
        $form = $validated['serviceVisitForm'];

        ServiceVisit::create([
            'user_name' => $filter->customer?->name ?? __('keywords.not_specified_arabic'),
            'maintenance_type' => $this->buildMaintenanceType($form['maintenance_input'], $this->serviceVisitDueCandles),
            'technician_name' => blank($form['technician_name'] ?? null) ? null : trim((string) $form['technician_name']),
            'cost' => filled($form['cost'] ?? null) ? $form['cost'] : null,
            'notes' => blank($form['notes'] ?? null) ? null : $form['notes'],
            'user_id' => auth()->id(),
            'water_filter_id' => $filter->id,
            'is_completed' => false,
        ]);

        $this->resetServiceVisitForm();
        $this->dispatch('close-modal-create-service-visit');
    }

    #[Computed]
    public function filters()
    {
        return $this->items;
    }

    #[Computed]
    public function customers(): Collection
    {
        return Customer::orderBy('name')->get();
    }

    #[Computed]
    public function placeOptions(): array
    {
        return Place::query()->orderBy('name')->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function candleFilterOptions(): array
    {
        return [
            'candle_1' => __('keywords.candle_1'),
            'candle_2_3' => __('keywords.candle_2_3'),
            'candle_4' => __('keywords.candle_4'),
            'candle_5' => __('keywords.candle_5'),
            'candle_6' => __('keywords.candle_6'),
            'candle_7' => __('keywords.candle_7'),
        ];
    }

    public function render()
    {
        return view('livewire.filters.filter-management');
    }

    protected function authorizeManageFilters(): void
    {
        abort_unless(auth()->user()?->can('manage_water_filters'), 403);
    }

    protected function selectedCandleFilters(): array
    {
        $allowedKeys = array_keys($this->candleFilterOptions);

        return collect($this->candleNeedsReplacement)
            ->filter(fn ($key) => is_string($key) && in_array($key, $allowedKeys, true))
            ->unique()
            ->values()
            ->all();
    }

    protected function applyCandleNeedsReplacementFilter(Builder $query, string $candleKey): void
    {
        match ($candleKey) {
            'candle_1' => $this->applyCandle1NeedsReplacementFilter($query),
            'candle_2_3' => $this->applyDateBasedCandleNeedsReplacementFilter($query, 'candle_2_3_replaced_at', 5),
            'candle_4' => $this->applyCandle4NeedsReplacementFilter($query),
            'candle_5' => $this->applyDateBasedCandleNeedsReplacementFilter($query, 'candle_5_replaced_at', 6),
            'candle_6' => $this->applyDateBasedCandleNeedsReplacementFilter($query, 'candle_6_replaced_at', 8),
            'candle_7' => $this->applyDateBasedCandleNeedsReplacementFilter($query, 'candle_7_replaced_at', 10),
            default => null,
        };
    }

    protected function applyDateBasedCandleNeedsReplacementFilter(Builder $query, string $replacedAtColumn, int $intervalMonths): void
    {
        $table = $query->getModel()->getTable();

        $query->where("{$table}.is_installed", true)
            ->whereNotNull("{$table}.installed_at")
            ->whereRaw(
                "DATE_ADD(COALESCE({$table}.{$replacedAtColumn}, {$table}.installed_at), INTERVAL ? MONTH) <= NOW()",
                [$intervalMonths]
            );
    }

    protected function applyCandle1NeedsReplacementFilter(Builder $query): void
    {
        $table = $query->getModel()->getTable();

        $query->where("{$table}.is_installed", true)
            ->whereNotNull("{$table}.installed_at")
            ->whereRaw(
                "DATE_ADD(\n                    COALESCE({$table}.candle_1_replaced_at, {$table}.installed_at),\n                    INTERVAL CASE (\n                        SELECT pre_reading.water_quality\n                        FROM water_readings AS pre_reading\n                        WHERE pre_reading.water_filter_id = {$table}.id\n                          AND pre_reading.before_installment = 1\n                        ORDER BY pre_reading.created_at ASC, pre_reading.id ASC\n                        LIMIT 1\n                    )\n                        WHEN 'good' THEN 3\n                        WHEN 'fair' THEN 2\n                        WHEN 'poor' THEN 1\n                        ELSE 3\n                    END MONTH\n                ) <= NOW()"
            );
    }

    protected function applyCandle4NeedsReplacementFilter(Builder $query): void
    {
        $table = $query->getModel()->getTable();

        $query->where("{$table}.is_installed", true)
            ->whereNotNull("{$table}.installed_at")
            ->whereExists(function ($existsQuery) use ($table) {
                $existsQuery->selectRaw('1')
                    ->from('water_readings as latest_reading')
                    ->whereColumn('latest_reading.water_filter_id', "{$table}.id")
                    ->whereRaw(
                        "latest_reading.id = (\n                        SELECT wr.id\n                        FROM water_readings AS wr\n                        WHERE wr.water_filter_id = {$table}.id\n                        ORDER BY wr.created_at DESC, wr.id DESC\n                        LIMIT 1\n                    )"
                    )
                    ->where('latest_reading.tds', '>=', 100);
            });
    }

    protected function resetServiceVisitForm(): void
    {
        $this->serviceVisitFilterId = null;
        $this->serviceVisitDueCandles = [];
        $this->serviceVisitForm = [
            'maintenance_input' => '',
            'technician_name' => '',
            'cost' => '',
            'notes' => '',
        ];
    }

    protected function dueCandleLabels(WaterFilter $filter): array
    {
        $status = $filter->candle_status;

        $labelMap = [
            'candle_1' => '1',
            'candle_2_3' => '2/3',
            'candle_4' => '4',
            'candle_5' => '5',
            'candle_6' => '6',
            'candle_7' => '7',
        ];

        return collect($labelMap)
            ->filter(fn (string $label, string $key) => ($status[$key] ?? null) === 'danger')
            ->values()
            ->all();
    }

    protected function buildMaintenanceType(?string $input, array $dueCandleLabels): string
    {
        $maintenanceText = trim((string) ($input ?? ''));

        if ($dueCandleLabels === []) {
            return $maintenanceText === '' ? '—' : $maintenanceText;
        }

        $candlesText = collect($dueCandleLabels)
            ->map(fn (string $label) => "ش {$label}")
            ->implode(' - ');

        if ($maintenanceText === '') {
            return $candlesText;
        }

        return "{$candlesText} / {$maintenanceText}";
    }

    protected function authorizeManageServiceVisits(): void
    {
        abort_unless(auth()->user()?->can('manage_service_visits'), 403);
    }
}
