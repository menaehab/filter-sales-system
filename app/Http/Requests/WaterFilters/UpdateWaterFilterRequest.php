<?php

namespace App\Http\Requests\WaterFilters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWaterFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter_model' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'exists:customers,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'filter_model' => __('keywords.filter_model'),
            'address' => __('keywords.address'),
            'customer_id' => __('keywords.customer'),
        ];
    }
}
