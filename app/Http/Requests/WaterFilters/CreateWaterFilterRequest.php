<?php

namespace App\Http\Requests\WaterFilters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateWaterFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_water_filters') ?? false;
    }

    public function rules(): array
    {
        return [
            'filter_model' => ['required', 'string', 'max:255', Rule::unique('water_filters', 'filter_model')],
            'address' => ['required', 'string', 'max:255'],
            'is_installed' => ['required', 'boolean'],
            'installed_at' => ['nullable', 'date', 'required_if:is_installed,1'],
            'customer_id' => ['required', 'exists:customers,id', Rule::unique('water_filters', 'customer_id')],
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
