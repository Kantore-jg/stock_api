<?php

namespace App\Http\Resources;

use App\Models\NumberSequence;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NumberSequence
 */
class NumberSequenceResource extends JsonResource
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
            'document_type' => $this->document_type,
            'prefix' => $this->prefix,
            'next_number' => $this->next_number,
            'padding' => $this->padding,
            'updated_at' => $this->updated_at,
        ];
    }
}
