<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('product_labels.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'default_tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'tax_included_in_price' => ['sometimes', 'boolean'],
            'is_tax_exempt' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
