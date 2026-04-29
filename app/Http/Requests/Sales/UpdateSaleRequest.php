<?php

namespace App\Http\Requests\Sales;

use App\Enums\PaymentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAny(['manage_sales', 'edit_sales']) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_type' => ['required', 'in:'.implode(',', array_column(PaymentTypeEnum::cases(), 'value'))],
            'down_payment' => ['required_if:payment_type,installment', 'nullable', 'numeric', 'min:0'],
            'installment_months' => ['required_if:payment_type,installment', 'nullable', 'integer', 'min:1', 'max:60'],
            'interest_rate' => ['required_if:payment_type,installment', 'nullable', 'numeric', 'min:0', 'max:100'],
            'installment_start_date' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'with_vat' => ['boolean'],
            'dealer_name' => ['nullable', 'string', 'max:255'],
            'created_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.sell_price' => ['required', 'numeric', 'min:0.01'],
            'items.*.cost_price' => ['nullable', 'numeric'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id' => __('keywords.customer'),
            'payment_type' => __('keywords.payment_type'),
            'down_payment' => __('keywords.down_payment'),
            'installment_months' => __('keywords.installment_months'),
            'interest_rate' => __('keywords.interest_rate'),
            'installment_start_date' => __('keywords.installment_start_date'),
            'with_vat' => __('keywords.with_vat'),
            'dealer_name' => __('keywords.dealer_name'),
            'created_at' => __('keywords.created_at'),
            'items' => __('keywords.cart'),
            'items.*.product_id' => __('keywords.product'),
            'items.*.sell_price' => __('keywords.sell_price'),
            'items.*.quantity' => __('keywords.quantity'),
        ];
    }
}
