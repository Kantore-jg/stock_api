<?php

namespace App\Http\Requests;

use App\Enums\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('stock_movements.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'movement_type' => ['required', Rule::enum(StockMovementType::class)],
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
            'document_date' => ['required', 'date'],
            'reference' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('stock_movements', 'reference')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'lines.*.note' => ['nullable', 'string'],
        ];
    }
}
