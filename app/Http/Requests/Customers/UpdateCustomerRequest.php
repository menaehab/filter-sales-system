<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage_customers') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:11', 'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/'],
            'national_number' => ['nullable', 'string', 'max:14', 'min:14'],
            'address' => ['nullable', 'string', 'max:255'],
            'place_id' => ['required', 'integer', 'exists:places,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'national_number' => __('keywords.national_number'),
            'name' => __('keywords.name'),
            'phone' => __('keywords.phone'),
            'address' => __('keywords.address'),
            'place_id' => __('keywords.places'),
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must be a valid Egyptian phone number.',
        ];
    }
}
