<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'code',
    'name',
    'phone',
    'email',
    'address',
    'tax_number',
    'credit_limit',
    'currency_id',
    'notes',
    'is_active',
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
