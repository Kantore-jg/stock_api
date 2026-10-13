<?php

namespace App\Models;

use App\Enums\CashMovementType;
use Database\Factories\CashMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'cash_session_id',
    'movement_type',
    'amount',
    'reference_type',
    'reference_id',
    'description',
    'created_by',
])]
class CashMovement extends Model
{
    /** @use HasFactory<CashMovementFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'movement_type' => CashMovementType::class,
            'amount' => 'decimal:4',
        ];
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
