<?php

namespace App\Http\Resources;

use App\Models\CashMovement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CashMovement
 */
class CashMovementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cash_session_id' => $this->cash_session_id,
            'movement_type' => $this->movement_type,
            'amount' => $this->amount,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
        ];
    }
}
