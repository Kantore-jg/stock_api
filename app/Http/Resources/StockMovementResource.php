<?php

namespace App\Http\Resources;

use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StockMovement
 */
class StockMovementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'reference' => $this->reference,
            'movement_type' => $this->movement_type,
            'status' => $this->status,
            'source_stock_id' => $this->source_stock_id,
            'destination_stock_id' => $this->destination_stock_id,
            'reason' => $this->reason,
            'document_date' => $this->document_date,
            'created_by' => $this->created_by,
            'validated_by' => $this->validated_by,
            'validated_at' => $this->validated_at,
            'cancelled_by' => $this->cancelled_by,
            'cancelled_at' => $this->cancelled_at,
            'cancellation_reason' => $this->cancellation_reason,
            'source_stock' => new StockResource($this->whenLoaded('sourceStock')),
            'destination_stock' => new StockResource($this->whenLoaded('destinationStock')),
            'lines' => StockMovementLineResource::collection($this->whenLoaded('lines')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
