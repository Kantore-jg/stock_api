<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sales.pay') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => ['required', 'integer', Rule::exists('payment_methods', 'id')],
            'currency_id' => ['nullable', 'integer', Rule::exists('currencies', 'id')],
            'amount' => ['required', 'numeric', 'gt:0'],
            'exchange_rate' => ['sometimes', 'numeric', 'gt:0'],
            'reference' => ['nullable', 'string', 'max:120'],
            'payment_date' => ['required', 'date'],
            'cash_session_id' => ['nullable', 'integer', Rule::exists('cash_sessions', 'id')],
            'notes' => ['nullable', 'string'],
        ];
    }
}
