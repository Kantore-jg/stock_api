<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id',
    'stock_id',
    'expense_category_id',
    'cash_session_id',
    'reference',
    'description',
    'amount',
    'currency_id',
    'payment_method_id',
    'expense_date',
    'status',
    'receipt_path',
    'created_by',
    'validated_by',
])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ExpenseStatus::class,
            'amount' => 'decimal:4',
            'expense_date' => 'datetime',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === ExpenseStatus::Draft;
    }

    public function isValidated(): bool
    {
        return $this->status === ExpenseStatus::Validated;
    }
}
