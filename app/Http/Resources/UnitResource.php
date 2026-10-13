<?php

namespace App\Http\Resources;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Unit
 */
class UnitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'allows_decimal' => $this->allows_decimal,
            'is_active' => $this->is_active,
        ];
    }
}
