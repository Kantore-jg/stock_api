<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignStockUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('stocks.assign_users') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'user_id' => [
                Rule::requiredIf(fn () => $this->isMethod('post')),
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'can_view' => ['sometimes', 'boolean'],
            'can_manage_stock' => ['sometimes', 'boolean'],
            'can_sell' => ['sometimes', 'boolean'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
