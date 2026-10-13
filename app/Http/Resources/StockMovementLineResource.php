<?php

namespace App\Http\Resources;

use App\Models\StockMovementLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StockMovementLine
 */
class StockMovementLineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stock_movement_id' => $this->stock_movement_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,
            'source_quantity_before' => $this->source_quantity_before,
            'source_quantity_after' => $this->source_quantity_after,
            'destination_quantity_before' => $this->destination_quantity_before,
            'destination_quantity_after' => $this->destination_quantity_after,
            'note' => $this->note,
            'product' => new ProductResource($this->whenLoaded('product')),
            'created_at' => $this->created_at,
        ];
    }
}
