<?php

namespace App\Http\Requests\PurchaseReturns;

use Illuminate\Foundation\Http\FormRequest;

class CreatePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAny(['manage_purchase_returns', 'add_purchase_returns']) ?? false;
    }

    public function rules(): array
    {
        return [
            'purchase_id' => ['required', 'exists:purchases,id'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'cash_refund' => ['boolean'],
            'created_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required', 'exists:purchase_items,id'],
            'items.*.return_quantity' => ['required', 'integer', 'min:1', 'lte:items.*.available_quantity'],
            'items.*.available_quantity' => ['nullable', 'numeric'],
        ];
    }

    public function attributes(): array
    {
        return [
            'purchase_id' => __('keywords.purchase'),
            'reason' => __('keywords.reason'),
            'cash_refund' => __('keywords.cash_refund'),
            'created_at' => __('keywords.created_at'),
            'items.*.purchase_item_id' => __('keywords.purchase_item'),
            'items.*.return_quantity' => __('keywords.return_quantity'),
        ];
    }
}
