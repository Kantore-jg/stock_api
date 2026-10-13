<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('stocks.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;
        $stockId = $this->route('stock')?->id ?? $this->route('stock');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:40',
                Rule::unique('stocks', 'code')
                    ->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at'))
                    ->ignore($stockId),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
