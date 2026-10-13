<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('stocks.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'code' => [
                'required',
                'string',
                'max:40',
                Rule::unique('stocks', 'code')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
