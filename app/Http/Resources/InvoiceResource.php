<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date,
            'status' => $this->status,
            'customer_name_snapshot' => $this->customer_name_snapshot,
            'customer_address_snapshot' => $this->customer_address_snapshot,
            'customer_tax_number_snapshot' => $this->customer_tax_number_snapshot,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'currency_id' => $this->currency_id,
            'issued_by' => $this->issued_by,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'sale' => new SaleResource($this->whenLoaded('sale')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
