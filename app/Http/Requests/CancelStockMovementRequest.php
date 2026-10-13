<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('stock_movements.cancel') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cancellation_reason' => ['nullable', 'string'],
        ];
    }
}
