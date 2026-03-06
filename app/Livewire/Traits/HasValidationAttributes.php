<?php

namespace App\Livewire\Traits;

trait HasValidationAttributes
{
    abstract protected function validationAttributes(): array;

    protected function getValidationAttributes(): array
    {
        return collect($this->validationAttributes())
            ->mapWithKeys(function ($value, $key) {
                if (is_string($key)) {
                    return [$key => $value];
                }

                return [$value => __('keywords.' . $value)];
            })
            ->toArray();
    }
}
