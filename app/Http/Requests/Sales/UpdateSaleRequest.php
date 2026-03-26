<?php

namespace App\Http\Requests\Sales;

use App\Enums\PaymentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_type' => ['required', 'in:'.implode(',', array_column(PaymentTypeEnum::cases(), 'value'))],
            'down_payment' => ['required_if:payment_type,installment', 'nullable', 'numeric', 'min:0'],
            'installment_months' => ['required_if:payment_type,installment', 'nullable', 'integer', 'min:1', 'max:60'],
            'interest_rate' => ['required_if:payment_type,installment', 'nullable', 'numeric', 'min:0', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'with_vat' => ['boolean'],
            'dealer_name' => ['nullable', 'string', 'max:255'],
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.product_id' => ['required', 'exists:products,id'],
            'cart.*.sell_price' => ['required', 'numeric', 'min:0.01'],
            'cart.*.cost_price' => ['nullable', 'numeric'],
            'cart.*.quantity' => ['required', 'integer', 'min:1'],
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
            'with_vat' => __('keywords.with_vat'),
            'dealer_name' => __('keywords.dealer_name'),
            'cart' => __('keywords.cart'),
            'cart.*.product_id' => __('keywords.product'),
            'cart.*.sell_price' => __('keywords.sell_price'),
            'cart.*.quantity' => __('keywords.quantity'),
        ];
    }
}
