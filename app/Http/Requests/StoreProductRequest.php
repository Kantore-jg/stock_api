<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'product_label_id' => [
                'required',
                'integer',
                Rule::exists('product_labels', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'barcode' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'purchase_price' => ['sometimes', 'numeric', 'min:0'],
            'selling_price' => ['sometimes', 'numeric', 'min:0'],
            'currency_id' => ['required', 'integer', Rule::exists('currencies', 'id')],
            'min_stock_level' => ['sometimes', 'numeric', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
