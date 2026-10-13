<?php

namespace App\Http\Resources;

use App\Models\ProductLabel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductLabel
 */
class ProductLabelResource extends JsonResource
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
            'description' => $this->description,
            'default_tax_rate' => $this->default_tax_rate,
            'tax_included_in_price' => $this->tax_included_in_price,
            'is_tax_exempt' => $this->is_tax_exempt,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
