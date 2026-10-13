<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'category_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'product_label_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('product_labels', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'unit_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('units', 'id')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at'))
                    ->ignore($productId),
            ],
            'barcode' => ['nullable', 'string', 'max:100'],
            'name' => ['sometimes', 'required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'purchase_price' => ['sometimes', 'numeric', 'min:0'],
            'selling_price' => ['sometimes', 'numeric', 'min:0'],
            'currency_id' => ['sometimes', 'required', 'integer', Rule::exists('currencies', 'id')],
            'min_stock_level' => ['sometimes', 'numeric', 'min:0'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
