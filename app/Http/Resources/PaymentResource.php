<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'sale_id' => $this->sale_id,
            'payment_method_id' => $this->payment_method_id,
            'currency_id' => $this->currency_id,
            'amount' => $this->amount,
            'exchange_rate' => $this->exchange_rate,
            'reference' => $this->reference,
            'payment_date' => $this->payment_date,
            'status' => $this->status,
            'cash_session_id' => $this->cash_session_id,
            'received_by' => $this->received_by,
            'notes' => $this->notes,
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
