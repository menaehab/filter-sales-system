<?php

namespace App\Http\Requests\SaleReturns;

use Illuminate\Foundation\Http\FormRequest;

class CreateSaleReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAny(['manage_sale_returns', 'add_sale_returns']) ?? false;
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'exists:sales,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'cash_refund' => ['boolean'],
            'created_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.return_quantity' => ['required', 'integer', 'min:1', 'lte:items.*.available_quantity'],
            'items.*.available_quantity' => ['nullable', 'numeric'],
        ];
    }

    public function attributes(): array
    {
        return [
            'sale_id' => __('keywords.sale'),
            'cash_refund' => __('keywords.cash_refund'),
            'reason' => __('keywords.reason'),
            'created_at' => __('keywords.created_at'),
            'items.*.sale_item_id' => __('keywords.sale_item'),
            'items.*.return_quantity' => __('keywords.return_quantity'),
        ];
    }
}
