<?php

namespace App\Http\Resources;

use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CashRegister
 */
class CashRegisterResource extends JsonResource
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
            'name' => $this->name,
            'currency_id' => $this->currency_id,
            'is_active' => $this->is_active,
            'stock' => new StockResource($this->whenLoaded('stock')),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
