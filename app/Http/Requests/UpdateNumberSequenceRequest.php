<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNumberSequenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'stock_id' => [
                'nullable',
                'integer',
                Rule::exists('stocks', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'document_type' => ['sometimes', 'required', 'string', 'max:40'],
            'prefix' => ['sometimes', 'required', 'string', 'max:20'],
            'next_number' => ['sometimes', 'integer', 'min:1'],
            'padding' => ['sometimes', 'integer', 'min:1', 'max:12'],
        ];
    }
}
