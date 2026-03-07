<?php

namespace App\Livewire\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasCrudQuery
{
    abstract protected function getModelClass(): string;
    abstract protected function getSearchableFields(): array;

    protected function getWithRelations(): array
    {
        return [];
    }

    protected function applyAdditionalFilters(Builder $query): void
    {
    }

    public function getItemsProperty()
    {
        $query = $this->getModelClass()::query()->with($this->getWithRelations());

        $this->applyAdditionalFilters($query);

        if (filled($this->search)) {
            $query->where(function ($builder) {
                foreach ($this->getSearchableFields() as $index => $field) {
                    $this->applySearchCondition($builder, $field, $index === 0);
                }
            });
        }

        return $query->latest()->paginate($this->perPage);
    }

    protected function applySearchCondition(Builder $builder, string $field, bool $isFirst): void
    {
        if (str_contains($field, '.')) {
            $segments = explode('.', $field);
            $column = array_pop($segments);
            $relation = implode('.', $segments);

            if ($relation !== '') {
                $method = $isFirst ? 'whereHas' : 'orWhereHas';
                $builder->{$method}($relation, function (Builder $relationQuery) use ($column) {
                    $relationQuery->where($column, 'like', "%{$this->search}%");
                });

                return;
            }
        }

        $method = $isFirst ? 'where' : 'orWhere';
        $builder->{$method}($field, 'like', "%{$this->search}%");
    }
}
