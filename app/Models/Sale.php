<?php

namespace App\Models;

use App\Enums\SaleStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'company_id',
    'stock_id',
    'customer_id',
    'invoice_number',
    'status',
    'sale_date',
    'currency_id',
    'exchange_rate',
    'subtotal',
    'discount_amount',
    'tax_amount',
    'total_amount',
    'amount_paid',
    'balance_due',
    'notes',
    'stock_movement_id',
    'created_by',
    'validated_by',
    'validated_at',
    'cancelled_by',
    'cancelled_at',
    'cancellation_reason',
])]
class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SaleStatus::class,
            'sale_date' => 'datetime',
            'exchange_rate' => 'decimal:8',
            'subtotal' => 'decimal:4',
            'discount_amount' => 'decimal:4',
            'tax_amount' => 'decimal:4',
            'total_amount' => 'decimal:4',
            'amount_paid' => 'decimal:4',
            'balance_due' => 'decimal:4',
            'validated_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === SaleStatus::Draft;
    }

    public function isCancelled(): bool
    {
        return $this->status === SaleStatus::Cancelled;
    }

    public function acceptsPayments(): bool
    {
        return in_array($this->status, [
            SaleStatus::Validated,
            SaleStatus::PartiallyPaid,
            SaleStatus::Paid,
        ], true);
    }
}
