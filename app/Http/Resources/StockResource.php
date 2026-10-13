<?php

namespace App\Http\Resources;

use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Stock
 */
class StockResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'pivot' => $this->when($this->pivot, [
                'can_view' => (bool) ($this->pivot->can_view ?? false),
                'can_manage_stock' => (bool) ($this->pivot->can_manage_stock ?? false),
                'can_sell' => (bool) ($this->pivot->can_sell ?? false),
                'is_default' => (bool) ($this->pivot->is_default ?? false),
                'assigned_at' => $this->pivot->assigned_at ?? null,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
