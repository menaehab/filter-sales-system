<?php

namespace App\Http\Requests\Suppliers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_suppliers') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phones' => ['nullable', 'array'],
            'phones.*.number' => ['nullable', 'string', 'max:11', 'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('keywords.name'),
            'phones.*.number' => __('keywords.phone'),
        ];
    }
}
