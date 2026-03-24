<?php

namespace App\Livewire\ActivityLog;

use App\Livewire\Traits\HasCrudQuery;
use App\Livewire\Traits\WithSearchAndPagination;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.app', ['title' => 'activity_log_management'])]
class ActivityLogManagement extends Component
{
    use HasCrudQuery, WithSearchAndPagination;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?string $activityType = null;

    public ?string $modelType = null;

    public ?string $causerId = null;

    public array $expandedRows = [];

    protected function additionalQueryString(): array
    {
        return [
            'dateFrom' => ['as' => 'from', 'except' => ''],
            'dateTo' => ['as' => 'to', 'except' => ''],
            'activityType' => ['as' => 'type', 'except' => ''],
            'modelType' => ['as' => 'model', 'except' => ''],
            'causerId' => ['as' => 'user', 'except' => ''],
        ];
    }

    protected function getModelClass(): string
    {
        return Activity::class;
    }

    protected function getSearchableFields(): array
    {
        return ['description', 'subject_type'];
    }

    protected function applyAdditionalFilters(Builder $query): void
    {
        if (filled($this->dateFrom)) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if (filled($this->dateTo)) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        if (filled($this->activityType)) {
            $query->where('event', $this->activityType);
        }

        if (filled($this->modelType)) {
            $query->where('subject_type', $this->modelType);
        }

        if ($this->causerId === 'system') {
            $query->whereNull('causer_id');
        } elseif (filled($this->causerId)) {
            $query->where('causer_id', (int) $this->causerId);
        }
    }

    protected function defaultOrderColumn(): string
    {
        return 'created_at';
    }

    protected function defaultOrderDirection(): string
    {
        return 'desc';
    }

    public function getActivitiesProperty()
    {
        return $this->buildQuery()
            ->with(['causer', 'subject'])
            ->latest()
            ->paginate($this->perPage);
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingActivityType(): void
    {
        $this->resetPage();
    }

    public function updatingModelType(): void
    {
        $this->resetPage();
    }

    public function updatingCauserId(): void
    {
        $this->resetPage();
    }

    public function toggleRow(int $id)
    {
        if (in_array($id, $this->expandedRows)) {
            $this->expandedRows = array_diff($this->expandedRows, [$id]);
        } else {
            $this->expandedRows[] = $id;
        }
    }

    public function isRowExpanded(int $id): bool
    {
        return in_array($id, $this->expandedRows);
    }

    public function getAvailableModelsProperty(): array
    {
        return Activity::query()
            ->distinct()
            ->pluck('subject_type')
            ->filter()
            ->map(fn ($type) => ['value' => $type, 'label' => $this->translateModelType($type)])
            ->toArray();
    }

    public function getAvailableEventsProperty(): array
    {
        return Activity::query()
            ->distinct()
            ->pluck('event')
            ->filter()
            ->values()
            ->toArray();
    }

    public function getAvailableUsersProperty()
    {
        return User::query()->select(['id', 'name'])->orderBy('name')->get();
    }

    public function translateModelType(?string $modelType): string
    {
        if (blank($modelType)) {
            return __('keywords.not_available');
        }

        $baseName = class_basename($modelType);
        $snake = Str::snake($baseName);

        foreach ([
            'keywords.'.$snake,
            'keywords.'.Str::plural($snake),
            'keywords.'.$baseName,
            'keywords.'.Str::plural($baseName),
        ] as $key) {
            $translated = __($key);
            if ($translated !== $key) {
                return $translated;
            }
        }

        return $baseName;
    }

    public function translateEventType(?string $event): string
    {
        if (blank($event)) {
            return __('keywords.not_available');
        }

        $key = 'keywords.'.Str::snake($event);
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return Str::headline($event);
    }

    public function translateAttributeLabel(string $attribute): string
    {
        $normalized = Str::snake(str_replace('.', '_', $attribute));
        $key = 'keywords.'.$normalized;
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return Str::headline(str_replace(['.', '_'], ' ', $attribute));
    }

    public function formatAttributeValue(mixed $value): string
    {
        if (is_null($value)) {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'نعم' : 'لا';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_string($value) && strtotime($value) !== false) {
            try {
                return \Carbon\Carbon::parse($value)->diffForHumans();
            } catch (\Exception $e) {
                // Not a valid date string after all
            }
        }

        return (string) $value;
    }

    public function render()
    {
        return view('livewire.activity-log.activity-log-management');
    }
}
