<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'name',
    'abbreviation',
    'allows_decimal',
    'is_active',
])]
class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use BelongsToCompany, HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allows_decimal' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
