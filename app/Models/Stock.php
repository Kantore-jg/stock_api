<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\StockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'code',
    'name',
    'description',
    'address',
    'is_active',
    'created_by',
])]
class Stock extends Model
{
    /** @use HasFactory<StockFactory> */
    use BelongsToCompany, HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'stock_user')
            ->withPivot([
                'assigned_by',
                'can_view',
                'can_manage_stock',
                'can_sell',
                'is_default',
                'assigned_at',
            ]);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }
}
