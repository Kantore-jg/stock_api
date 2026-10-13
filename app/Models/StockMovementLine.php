<?php

namespace App\Models;

use Database\Factories\StockMovementLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'stock_movement_id',
    'product_id',
    'quantity',
    'unit_cost',
    'source_quantity_before',
    'source_quantity_after',
    'destination_quantity_before',
    'destination_quantity_after',
    'note',
])]
class StockMovementLine extends Model
{
    /** @use HasFactory<StockMovementLineFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'source_quantity_before' => 'decimal:4',
            'source_quantity_after' => 'decimal:4',
            'destination_quantity_before' => 'decimal:4',
            'destination_quantity_after' => 'decimal:4',
        ];
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'stock_movement_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
