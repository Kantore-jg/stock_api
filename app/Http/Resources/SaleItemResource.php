<?php

namespace App\Http\Resources;

use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SaleItem
 */
class SaleItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_id' => $this->sale_id,
            'product_id' => $this->product_id,
            'product_name_snapshot' => $this->product_name_snapshot,
            'product_sku_snapshot' => $this->product_sku_snapshot,
            'label_name_snapshot' => $this->label_name_snapshot,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_amount' => $this->discount_amount,
            'tax_rate_snapshot' => $this->tax_rate_snapshot,
            'tax_amount' => $this->tax_amount,
            'line_subtotal' => $this->line_subtotal,
            'line_total' => $this->line_total,
            'product' => new ProductResource($this->whenLoaded('product')),
            'created_at' => $this->created_at,
        ];
    }
}
