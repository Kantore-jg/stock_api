<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreTenantCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_super_admin === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:180'],
            'legal_name' => ['nullable', 'string', 'max:200'],
            'tax_number' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'string', 'max:40'],
            'company_email' => ['nullable', 'email', 'max:150'],
            'stock_name' => ['nullable', 'string', 'max:150'],
            'currency_code' => ['nullable', 'string', 'size:3', Rule::exists('currencies', 'code')],
            'admin_name' => ['required', 'string', 'max:180'],
            'email' => [
                'required',
                'email',
                'max:180',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
