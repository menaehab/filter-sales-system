<?php

namespace App\Http\Requests\DamagedProducts;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDamagedProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_damaged_products') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'product_id' => __('keywords.product'),
            'quantity' => __('keywords.quantity'),
            'reason' => __('keywords.reason'),
        ];
    }
}
