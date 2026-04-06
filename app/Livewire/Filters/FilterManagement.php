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
use App\Models\WaterFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class FilterManagement extends Component
{
    use HasCrudModals, HasCrudQuery, HasForm, WithSearchAndPagination;

    public $customerSlug = '';

    public $customerSearch = '';

    public $customerModalSearch = '';

    public array $candleNeedsReplacement = [];

    public function mount(): void
    {
        $this->resetForm();

        if ($this->customerSlug) {
            $this->customerSearch = $this->customers->firstWhere('slug', $this->customerSlug)?->name ?? '';
        }
    }

    protected function getDefaultForm(): array
    {
        return [
            'filter_model' => '',
            'address' => '',
            'customer_id' => null,
        ];
    }

    protected function getModelClass(): string
    {
        return WaterFilter::class;
    }

    protected function getSearchableFields(): array
    {
        return ['filter_model', 'address', 'customer.name', 'customer.phones.number'];
    }

    protected function getWithRelations(): array
    {
        return ['customer', 'customer.phones'];
    }

    protected function additionalQueryString(): array
    {
        return [
            'customerSlug' => ['as' => 'customer', 'except' => ''],
        ];
    }

    public function updatingCustomerSlug(): void
    {
        $this->resetPage();
        $this->customerSearch = $this->customers->firstWhere('slug', $this->customerSlug)?->name ?? '';
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

        $query->whereNotNull("{$table}.installed_at")
            ->whereRaw(
                "DATE_ADD(COALESCE({$table}.{$replacedAtColumn}, {$table}.installed_at), INTERVAL ? MONTH) <= NOW()",
                [$intervalMonths]
            );
    }

    protected function applyCandle1NeedsReplacementFilter(Builder $query): void
    {
        $table = $query->getModel()->getTable();

        $query->whereNotNull("{$table}.installed_at")
            ->whereRaw(
                "DATE_ADD(\n                    COALESCE({$table}.candle_1_replaced_at, {$table}.installed_at),\n                    INTERVAL CASE (\n                        SELECT pre_reading.water_quality\n                        FROM water_readings AS pre_reading\n                        WHERE pre_reading.water_filter_id = {$table}.id\n                          AND pre_reading.before_installment = 1\n                        ORDER BY pre_reading.created_at ASC, pre_reading.id ASC\n                        LIMIT 1\n                    )\n                        WHEN 'good' THEN 3\n                        WHEN 'fair' THEN 2\n                        WHEN 'poor' THEN 1\n                        ELSE 3\n                    END MONTH\n                ) <= NOW()"
            );
    }

    protected function applyCandle4NeedsReplacementFilter(Builder $query): void
    {
        $table = $query->getModel()->getTable();

        $query->whereExists(function ($existsQuery) use ($table) {
            $existsQuery->selectRaw('1')
                ->from('water_readings as latest_reading')
                ->whereColumn('latest_reading.water_filter_id', "{$table}.id")
                ->whereRaw(
                    "latest_reading.id = (\n                        SELECT wr.id\n                        FROM water_readings AS wr\n                        WHERE wr.water_filter_id = {$table}.id\n                        ORDER BY wr.created_at DESC, wr.id DESC\n                        LIMIT 1\n                    )"
                )
                ->where('latest_reading.tds', '>=', 100);
        });
    }
}
