<?php

namespace App\Http\Requests\SupplierPayments;

use App\Enums\PaymentMethodEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_supplier_payment_allocations') ?? false;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:'.implode(',', array_map(fn (PaymentMethodEnum $method) => $method->value, PaymentMethodEnum::supplierMethods()))],
            'note' => ['nullable', 'string', 'max:255'],
            'created_at' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => __('keywords.amount'),
            'payment_method' => __('keywords.payment_method'),
            'note' => __('keywords.note'),
            'created_at' => __('keywords.created_at'),
        ];
    }
}
