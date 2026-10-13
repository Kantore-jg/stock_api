<?php

namespace App\Http\Controllers\Api;

use App\Enums\CashMovementType;
use App\Http\Controllers\Controller;
use App\Http\Resources\CashMovementResource;
use App\Http\Resources\CashSessionResource;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Services\CashSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CashSessionController extends Controller
{
    public function __construct(
        private readonly CashSessionService $cashSessions,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('cash_sessions.view'), 403);

        $companyId = $request->user()->company_id;

        $sessions = CashSession::query()
            ->with(['cashRegister', 'user'])
            ->whereHas('cashRegister', fn ($query) => $query->where('company_id', $companyId))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->integer('cash_register_id'), fn ($query, int $id) => $query->where('cash_register_id', $id))
            ->latest('opened_at')
            ->paginate(20);

        return CashSessionResource::collection($sessions);
    }

    public function open(Request $request, CashRegister $cashRegister): JsonResponse
    {
        abort_unless($request->user()->can('cash_sessions.open'), 403);

        $data = $request->validate([
            'opening_amount' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $session = $this->cashSessions->open($cashRegister, $request->user(), $data);

        return (new CashSessionResource($session))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, CashSession $cashSession): CashSessionResource
    {
        abort_unless($request->user()->can('cash_sessions.view'), 403);
        $this->assertCompany($request, $cashSession);

        return new CashSessionResource($cashSession->load(['cashRegister', 'user', 'movements']));
    }

    public function close(Request $request, CashSession $cashSession): CashSessionResource
    {
        abort_unless($request->user()->can('cash_sessions.close'), 403);
        $this->assertCompany($request, $cashSession);

        $data = $request->validate([
            'counted_amount' => ['required', 'numeric', 'min:0'],
            'closing_notes' => ['nullable', 'string'],
        ]);

        $session = $this->cashSessions->close($cashSession, $request->user(), $data);

        return new CashSessionResource($session);
    }

    public function storeMovement(Request $request, CashSession $cashSession): JsonResponse
    {
        abort_unless($request->user()->can('cash_movements.create'), 403);
        $this->assertCompany($request, $cashSession);

        $data = $request->validate([
            'movement_type' => ['required', Rule::enum(CashMovementType::class)],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['nullable', 'string'],
        ]);

        $movement = $this->cashSessions->recordMovement(
            $cashSession,
            $request->user(),
            CashMovementType::from($data['movement_type']),
            (string) $data['amount'],
            $data['description'] ?? null,
        );

        return (new CashMovementResource($movement))
            ->response()
            ->setStatusCode(201);
    }

    private function assertCompany(Request $request, CashSession $cashSession): void
    {
        $cashSession->loadMissing('cashRegister');
        abort_unless(
            (int) $cashSession->cashRegister->company_id === (int) $request->user()->company_id,
            404,
        );
    }
}
