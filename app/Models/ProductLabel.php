<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ProductLabelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'name',
    'description',
    'default_tax_rate',
    'tax_included_in_price',
    'is_tax_exempt',
    'is_active',
])]
class ProductLabel extends Model
{
    /** @use HasFactory<ProductLabelFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_tax_rate' => 'decimal:4',
            'tax_included_in_price' => 'boolean',
            'is_tax_exempt' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
