<?php

namespace App\Http\Resources;

use App\Models\StockItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StockItem
 */
class StockItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stock_id' => $this->stock_id,
            'product_id' => $this->product_id,
            'quantity_on_hand' => $this->quantity_on_hand,
            'quantity_reserved' => $this->quantity_reserved,
            'quantity_available' => $this->availableQuantity(),
            'stock' => new StockResource($this->whenLoaded('stock')),
            'product' => new ProductResource($this->whenLoaded('product')),
            'updated_at' => $this->updated_at,
        ];
    }
}
