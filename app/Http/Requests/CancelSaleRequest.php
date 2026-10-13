<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sales.cancel') ?? false;
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
