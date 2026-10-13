<?php

namespace App\Models;

use Database\Factories\SaleItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sale_id',
    'product_id',
    'product_name_snapshot',
    'product_sku_snapshot',
    'label_name_snapshot',
    'quantity',
    'unit_price',
    'discount_amount',
    'tax_rate_snapshot',
    'tax_amount',
    'line_subtotal',
    'line_total',
])]
class SaleItem extends Model
{
    /** @use HasFactory<SaleItemFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount_amount' => 'decimal:4',
            'tax_rate_snapshot' => 'decimal:4',
            'tax_amount' => 'decimal:4',
            'line_subtotal' => 'decimal:4',
            'line_total' => 'decimal:4',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
