<?php

namespace App\Services;

use App\Enums\CashMovementType;
use App\Enums\ExpenseStatus;
use App\Models\CashSession;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function __construct(
        private readonly NumberSequenceService $numberSequences,
        private readonly CashSessionService $cashSessions,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Expense
    {
        return DB::transaction(function () use ($user, $data) {
            $status = ExpenseStatus::from($data['status'] ?? ExpenseStatus::Validated->value);
            $cashSession = null;

            if (! empty($data['cash_session_id'])) {
                $cashSession = CashSession::query()->findOrFail($data['cash_session_id']);
                if (! $cashSession->isOpen()) {
                    throw ValidationException::withMessages([
                        'cash_session_id' => ['La session de caisse doit être ouverte.'],
                    ]);
                }
            }

            $reference = $data['reference']
                ?? $this->numberSequences->next($user->company_id, 'expense', 'DEP-');

            $expense = Expense::query()->create([
                'company_id' => $user->company_id,
                'stock_id' => $data['stock_id'] ?? null,
                'expense_category_id' => $data['expense_category_id'],
                'cash_session_id' => $cashSession?->id,
                'reference' => $reference,
                'description' => $data['description'],
                'amount' => $data['amount'],
                'currency_id' => $data['currency_id'],
                'payment_method_id' => $data['payment_method_id'],
                'expense_date' => $data['expense_date'],
                'status' => $status,
                'receipt_path' => $data['receipt_path'] ?? null,
                'created_by' => $user->id,
                'validated_by' => $status === ExpenseStatus::Validated ? $user->id : null,
            ]);

            if ($status === ExpenseStatus::Validated && $cashSession) {
                $this->cashSessions->recordMovement(
                    $cashSession,
                    $user,
                    CashMovementType::Out,
                    (string) $expense->amount,
                    'Dépense '.$expense->reference,
                    $expense,
                );
            }

            $this->auditLogger->log('expense.created', $expense, null, $expense->toArray());

            return $expense->fresh(['category', 'currency', 'paymentMethod', 'cashSession', 'stock']);
        });
    }

    public function validate(Expense $expense, User $user): Expense
    {
        if (! $expense->isDraft()) {
            throw ValidationException::withMessages([
                'status' => ['Seule une dépense brouillon peut être validée.'],
            ]);
        }

        return DB::transaction(function () use ($expense, $user) {
            $expense = Expense::query()->whereKey($expense->id)->lockForUpdate()->firstOrFail();

            if ($expense->cash_session_id) {
                $session = CashSession::query()->findOrFail($expense->cash_session_id);
                $this->cashSessions->recordMovement(
                    $session,
                    $user,
                    CashMovementType::Out,
                    (string) $expense->amount,
                    'Dépense '.$expense->reference,
                    $expense,
                );
            }

            $expense->update([
                'status' => ExpenseStatus::Validated,
                'validated_by' => $user->id,
            ]);

            $this->auditLogger->log('expense.validated', $expense, null, $expense->toArray());

            return $expense->fresh(['category', 'currency', 'paymentMethod', 'cashSession', 'stock']);
        });
    }

    public function cancel(Expense $expense, User $user, ?string $reason = null): Expense
    {
        if ($expense->status === ExpenseStatus::Cancelled) {
            throw ValidationException::withMessages([
                'status' => ['Cette dépense est déjà annulée.'],
            ]);
        }

        return DB::transaction(function () use ($expense, $user, $reason) {
            $expense = Expense::query()->whereKey($expense->id)->lockForUpdate()->firstOrFail();
            $wasValidated = $expense->isValidated();

            if ($wasValidated && $expense->cash_session_id) {
                $session = CashSession::query()->findOrFail($expense->cash_session_id);
                $this->cashSessions->recordMovement(
                    $session,
                    $user,
                    CashMovementType::In,
                    (string) $expense->amount,
                    'Annulation dépense '.$expense->reference.($reason ? ' — '.$reason : ''),
                    $expense,
                );
            }

            $expense->update(['status' => ExpenseStatus::Cancelled]);

            $this->auditLogger->log('expense.cancelled', $expense, null, [
                'reason' => $reason,
                'expense' => $expense->toArray(),
            ]);

            return $expense->fresh(['category', 'currency', 'paymentMethod', 'cashSession', 'stock']);
        });
    }
}
