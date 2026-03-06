<?php

namespace App\Livewire\Traits;

trait HasCrudQuery
{
    abstract protected function getModelClass(): string;
    abstract protected function getSearchableFields(): array;

    public function getItemsProperty()
    {
        $query = $this->getModelClass()::query();

        if (filled($this->search)) {
            $query->where(function ($builder) {
                foreach ($this->getSearchableFields() as $index => $field) {
                    if ($index === 0) {
                        $builder->where($field, 'like', "%{$this->search}%");
                    } else {
                        $builder->orWhere($field, 'like', "%{$this->search}%");
                    }
                }
            });
        }

        return $query->latest()->paginate($this->perPage);
    }
}
