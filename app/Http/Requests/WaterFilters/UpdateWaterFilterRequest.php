<?php

namespace App\Http\Requests\WaterFilters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWaterFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_water_filters') ?? false;
    }

    public function rules(): array
    {
        $filterId = $this->route('water_filter')?->id
            ?? $this->route('filter')?->id
            ?? $this->route('id');

        return [
            'filter_model' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'is_installed' => ['required', 'boolean'],
            'installed_at' => ['nullable', 'date', 'required_if:is_installed,1'],
            'customer_id' => ['required', 'exists:customers,id', Rule::unique('water_filters', 'customer_id')->ignore($filterId)],
        ];
    }

    public function attributes(): array
    {
        return [
            'filter_model' => __('keywords.filter_model'),
            'address' => __('keywords.address'),
            'is_installed' => __('keywords.is_installed'),
            'installed_at' => __('keywords.installed_at'),
            'customer_id' => __('keywords.customer'),
        ];
    }
}
