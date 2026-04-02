<?php

namespace App\Http\Requests\SupplierPayments;

use App\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;

class CreateSupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAny([
            'manage_supplier_payment_allocations',
            'manage_purchases',
            'pay_purchases',
        ]) ?? false;
    }

    public function rules(): array
    {
        return [
            'purchase_id' => ['required', 'exists:purchases,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:'.implode(',', array_column(PaymentMethodEnum::cases(), 'value'))],
            'note' => ['nullable', 'string', 'max:255'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'purchase_id' => __('keywords.purchase'),
            'payment_method' => __('keywords.payment_method'),
            'amount' => __('keywords.amount'),
            'created_at' => __('keywords.created_at'),
        ];
    }
}
