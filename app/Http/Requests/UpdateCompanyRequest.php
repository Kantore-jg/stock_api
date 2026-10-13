<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('company.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:180'],
            'legal_name' => ['nullable', 'string', 'max:200'],
            'tax_number' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'default_currency_id' => ['nullable', 'integer', Rule::exists('currencies', 'id')],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
