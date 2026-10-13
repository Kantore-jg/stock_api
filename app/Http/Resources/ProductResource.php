<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'category_id' => $this->category_id,
            'product_label_id' => $this->product_label_id,
            'unit_id' => $this->unit_id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'currency_id' => $this->currency_id,
            'min_stock_level' => $this->min_stock_level,
            'image_path' => $this->image_path,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'product_label' => new ProductLabelResource($this->whenLoaded('productLabel')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
