<?php

namespace App\Http\Resources;

use App\Models\CashSession;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CashSession
 */
class CashSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cash_register_id' => $this->cash_register_id,
            'user_id' => $this->user_id,
            'opening_amount' => $this->opening_amount,
            'expected_amount' => $this->expected_amount,
            'counted_amount' => $this->counted_amount,
            'difference_amount' => $this->difference_amount,
            'status' => $this->status,
            'opened_at' => $this->opened_at,
            'closed_at' => $this->closed_at,
            'closing_notes' => $this->closing_notes,
            'cash_register' => new CashRegisterResource($this->whenLoaded('cashRegister')),
            'user' => new UserResource($this->whenLoaded('user')),
            'movements' => CashMovementResource::collection($this->whenLoaded('movements')),
        ];
    }
}
