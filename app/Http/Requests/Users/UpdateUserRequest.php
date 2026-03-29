<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->input('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['nullable', 'string'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
                'required_without:phone',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:11',
                'regex:/^(\+201|01|00201)[0-2,5]{1}[0-9]{8}$/',
                Rule::unique('users', 'phone')->ignore($userId),
                'required_without:email',
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'place_ids' => ['nullable', 'array'],
            'place_ids.*' => ['integer', 'exists:places,id'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    public function attributes()
    {
        return [
            'name' => __('keywords.name'),
            'role' => __('keywords.role'),
            'email' => __('keywords.email'),
            'phone' => __('keywords.phone'),
            'password' => __('keywords.password'),
            'place_ids' => __('keywords.places'),
            'permissions' => __('keywords.permissions'),
        ];
    }
}
