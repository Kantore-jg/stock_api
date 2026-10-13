<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\CashRegisterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'stock_id',
    'name',
    'currency_id',
    'is_active',
])]
class CashRegister extends Model
{
    /** @use HasFactory<CashRegisterFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CashSession::class);
    }
}
