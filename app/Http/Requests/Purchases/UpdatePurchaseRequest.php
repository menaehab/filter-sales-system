<?php

namespace App\Http\Requests\Purchases;

use App\Enums\PaymentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAny(['manage_purchases', 'edit_purchases']) ?? false;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'payment_type' => ['required', 'in:'.implode(',', array_column(PaymentTypeEnum::cases(), 'value'))],
            'down_payment' => ['required_if:payment_type,installment', 'nullable', 'numeric', 'min:0'],
            'installment_months' => ['required_if:payment_type,installment', 'nullable', 'integer', 'min:1', 'max:60'],
            'created_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.cost_price' => ['required', 'numeric', 'min:0.01'],
            'items.*.sell_price' => ['required', 'numeric', 'min:0.01'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'supplier_id' => __('keywords.supplier'),
            'payment_type' => __('keywords.payment_type'),
            'down_payment' => __('keywords.down_payment'),
            'installment_months' => __('keywords.installment_months'),
            'created_at' => __('keywords.created_at'),
            'items' => __('keywords.purchase_items'),
            'items.*.product_id' => __('keywords.product'),
            'items.*.cost_price' => __('keywords.cost_price'),
            'items.*.sell_price' => __('keywords.sell_price'),
            'items.*.quantity' => __('keywords.quantity'),
        ];
    }
}
