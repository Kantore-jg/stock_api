<?php

namespace App\Models;

use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'symbol', 'decimal_places', 'is_active'])]
class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'decimal_places' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'default_currency_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
