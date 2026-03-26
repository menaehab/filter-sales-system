<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'min_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('keywords.name'),
            'quantity' => __('keywords.quantity'),
            'description' => __('keywords.description'),
            'cost_price' => __('keywords.cost_price'),
            'min_quantity' => __('keywords.min_quantity'),
            'category_id' => __('keywords.category'),
        ];
    }
}
