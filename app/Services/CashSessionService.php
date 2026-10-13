<?php

namespace App\Services;

use App\Enums\CashMovementType;
use App\Enums\CashSessionStatus;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashSessionService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function open(CashRegister $register, User $user, array $data): CashSession
    {
        $existing = CashSession::query()
            ->where('cash_register_id', $register->id)
            ->where('status', CashSessionStatus::Open)
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'cash_register_id' => ['Une session est déjà ouverte sur cette caisse.'],
            ]);
        }

        return DB::transaction(function () use ($register, $user, $data) {
            $session = CashSession::query()->create([
                'cash_register_id' => $register->id,
                'user_id' => $user->id,
                'opening_amount' => $data['opening_amount'] ?? 0,
                'status' => CashSessionStatus::Open,
                'opened_at' => now(),
            ]);

            $this->auditLogger->log('cash_session.opened', $session, null, $session->toArray(), $register->company_id);

            return $session->load(['cashRegister', 'user', 'movements']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function close(CashSession $session, User $user, array $data): CashSession
    {
        if (! $session->isOpen()) {
            throw ValidationException::withMessages([
                'status' => ['Cette session est déjà fermée.'],
            ]);
        }

        return DB::transaction(function () use ($session, $data) {
            $session = CashSession::query()->whereKey($session->id)->lockForUpdate()->firstOrFail();
            $session->load('movements');

            $expected = $this->calculateExpectedAmount($session);
            $counted = (string) $data['counted_amount'];
            $difference = bcsub($counted, $expected, 4);

            $session->update([
                'expected_amount' => $expected,
                'counted_amount' => $counted,
                'difference_amount' => $difference,
                'status' => CashSessionStatus::Closed,
                'closed_at' => now(),
                'closing_notes' => $data['closing_notes'] ?? null,
            ]);

            $session->load('cashRegister');

            $this->auditLogger->log(
                'cash_session.closed',
                $session,
                null,
                $session->toArray(),
                $session->cashRegister->company_id,
            );

            return $session->fresh(['cashRegister', 'user', 'movements']);
        });
    }

    public function recordMovement(
        CashSession $session,
        User $user,
        CashMovementType $type,
        string $amount,
        ?string $description = null,
        ?Model $reference = null,
    ): CashMovement {
        if (! $session->isOpen()) {
            throw ValidationException::withMessages([
                'cash_session_id' => ['La session de caisse doit être ouverte.'],
            ]);
        }

        if (bccomp($amount, '0', 4) <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['Le montant doit être positif.'],
            ]);
        }

        return CashMovement::query()->create([
            'cash_session_id' => $session->id,
            'movement_type' => $type,
            'amount' => $amount,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'description' => $description,
            'created_by' => $user->id,
        ]);
    }

    public function calculateExpectedAmount(CashSession $session): string
    {
        $total = (string) $session->opening_amount;

        foreach ($session->movements as $movement) {
            $amount = (string) $movement->amount;

            $total = match ($movement->movement_type) {
                CashMovementType::In, CashMovementType::Adjustment => bcadd($total, $amount, 4),
                CashMovementType::Out => bcsub($total, $amount, 4),
            };
        }

        return $total;
    }
}
