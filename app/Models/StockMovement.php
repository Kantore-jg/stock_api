<?php

namespace App\Models;

use App\Enums\StockMovementStatus;
use App\Enums\StockMovementType;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id',
    'reference',
    'movement_type',
    'status',
    'source_stock_id',
    'destination_stock_id',
    'reason',
    'document_date',
    'created_by',
    'validated_by',
    'validated_at',
    'cancelled_by',
    'cancelled_at',
    'cancellation_reason',
])]
class StockMovement extends Model
{
    /** @use HasFactory<StockMovementFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'movement_type' => StockMovementType::class,
            'status' => StockMovementStatus::class,
            'document_date' => 'datetime',
            'validated_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(StockMovementLine::class);
    }

    public function sourceStock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'source_stock_id');
    }

    public function destinationStock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'destination_stock_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isDraft(): bool
    {
        return $this->status === StockMovementStatus::Draft;
    }

    public function isValidated(): bool
    {
        return $this->status === StockMovementStatus::Validated;
    }
}
