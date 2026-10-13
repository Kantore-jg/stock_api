<?php

namespace App\Models;

use Database\Factories\StockItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'stock_id',
    'product_id',
    'quantity_on_hand',
    'quantity_reserved',
])]
class StockItem extends Model
{
    /** @use HasFactory<StockItemFactory> */
    use HasFactory;

    public const CREATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_on_hand' => 'decimal:4',
            'quantity_reserved' => 'decimal:4',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function availableQuantity(): string
    {
        return bcsub((string) $this->quantity_on_hand, (string) $this->quantity_reserved, 4);
    }
}
