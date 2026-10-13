<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'category_id',
    'product_label_id',
    'unit_id',
    'sku',
    'barcode',
    'name',
    'description',
    'purchase_price',
    'selling_price',
    'currency_id',
    'min_stock_level',
    'image_path',
    'is_active',
    'created_by',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:4',
            'selling_price' => 'decimal:4',
            'min_stock_level' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productLabel(): BelongsTo
    {
        return $this->belongsTo(ProductLabel::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }
}
