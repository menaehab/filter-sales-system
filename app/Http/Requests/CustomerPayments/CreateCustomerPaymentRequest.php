<?php

namespace App\Http\Requests\CustomerPayments;

use App\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;

class CreateCustomerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => ['required', 'exists:sales,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:'.implode(',', array_column(PaymentMethodEnum::cases(), 'value'))],
        ];
    }

    public function attributes(): array
    {
        return [
            'sale_id' => __('keywords.sale'),
            'payment_method' => __('keywords.payment_method'),
            'amount' => __('keywords.amount'),
        ];
    }
}
