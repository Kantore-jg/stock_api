<?php

namespace App\Http\Resources;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Sale
 */
class SaleResource extends JsonResource
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
            'customer_id' => $this->customer_id,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'sale_date' => $this->sale_date,
            'currency_id' => $this->currency_id,
            'exchange_rate' => $this->exchange_rate,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'amount_paid' => $this->amount_paid,
            'balance_due' => $this->balance_due,
            'notes' => $this->notes,
            'stock_movement_id' => $this->stock_movement_id,
            'created_by' => $this->created_by,
            'validated_by' => $this->validated_by,
            'validated_at' => $this->validated_at,
            'cancelled_by' => $this->cancelled_by,
            'cancelled_at' => $this->cancelled_at,
            'cancellation_reason' => $this->cancellation_reason,
            'stock' => new StockResource($this->whenLoaded('stock')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
