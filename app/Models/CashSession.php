<?php

namespace App\Models;

use App\Enums\CashSessionStatus;
use Database\Factories\CashSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'cash_register_id',
    'user_id',
    'opening_amount',
    'expected_amount',
    'counted_amount',
    'difference_amount',
    'status',
    'opened_at',
    'closed_at',
    'closing_notes',
])]
class CashSession extends Model
{
    /** @use HasFactory<CashSessionFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CashSessionStatus::class,
            'opening_amount' => 'decimal:4',
            'expected_amount' => 'decimal:4',
            'counted_amount' => 'decimal:4',
            'difference_amount' => 'decimal:4',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function isOpen(): bool
    {
        return $this->status === CashSessionStatus::Open;
    }
}
