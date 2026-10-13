<?php

namespace App\Http\Resources;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Expense
 */
class ExpenseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'stock_id' => $this->stock_id,
            'expense_category_id' => $this->expense_category_id,
            'cash_session_id' => $this->cash_session_id,
            'reference' => $this->reference,
            'description' => $this->description,
            'amount' => $this->amount,
            'currency_id' => $this->currency_id,
            'payment_method_id' => $this->payment_method_id,
            'expense_date' => $this->expense_date,
            'status' => $this->status,
            'receipt_path' => $this->receipt_path,
            'created_by' => $this->created_by,
            'validated_by' => $this->validated_by,
            'category' => new ExpenseCategoryResource($this->whenLoaded('category')),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'cash_session' => new CashSessionResource($this->whenLoaded('cashSession')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
