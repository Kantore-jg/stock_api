<?php

namespace App\Http\Requests;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('stock_movements.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'movement_type' => ['sometimes', 'required', Rule::enum(StockMovementType::class)],
            'source_stock_id' => [
                'nullable',
                'integer',
                Rule::exists('stocks', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'destination_stock_id' => [
                'nullable',
                'integer',
                Rule::exists('stocks', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'reason' => ['nullable', 'string'],
            'document_date' => ['sometimes', 'required', 'date'],
            'lines' => ['sometimes', 'array', 'min:1'],
            'lines.*.product_id' => [
                'required_with:lines',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'lines.*.quantity' => ['required_with:lines', 'numeric', 'gt:0'],
            'lines.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'lines.*.note' => ['nullable', 'string'],
        ];
    }
}
