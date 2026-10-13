<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelStockMovementRequest;
use App\Http\Requests\StoreStockMovementRequest;
use App\Http\Requests\UpdateStockMovementRequest;
use App\Http\Resources\StockMovementResource;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockMovementController extends Controller
{
    public function __construct(
        private readonly StockMovementService $stockMovements,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('stock_movements.view'), 403);

        $movements = StockMovement::query()
            ->with(['lines.product', 'sourceStock', 'destinationStock'])
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->when($request->string('movement_type')->toString(), fn ($query, string $type) => $query->where('movement_type', $type))
            ->when($request->integer('stock_id'), function ($query, int $stockId) {
                $query->where(function ($inner) use ($stockId) {
                    $inner->where('source_stock_id', $stockId)
                        ->orWhere('destination_stock_id', $stockId);
                });
            })
            ->latest('document_date')
            ->paginate(20);

        return StockMovementResource::collection($movements);
    }

    public function store(StoreStockMovementRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $lines = $validated['lines'];
        unset($validated['lines']);

        $movement = $this->stockMovements->createDraft($request->user(), $validated, $lines);

        return (new StockMovementResource($movement))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, StockMovement $stockMovement): StockMovementResource
    {
        abort_unless($request->user()->can('stock_movements.view'), 403);

        $stockMovement->load(['lines.product', 'sourceStock', 'destinationStock', 'creator', 'validator', 'canceller']);

        return new StockMovementResource($stockMovement);
    }

    public function update(UpdateStockMovementRequest $request, StockMovement $stockMovement): StockMovementResource
    {
        $validated = $request->validated();
        $lines = $validated['lines'] ?? null;
        unset($validated['lines']);

        $movement = $this->stockMovements->updateDraft($stockMovement, $validated, $lines);

        return new StockMovementResource($movement);
    }

    public function destroy(Request $request, StockMovement $stockMovement): JsonResponse
    {
        abort_unless($request->user()->can('stock_movements.update'), 403);
        abort_unless($stockMovement->isDraft(), 422, 'Seul un brouillon peut être supprimé.');

        $stockMovement->lines()->delete();
        $stockMovement->delete();

        return response()->json(['message' => 'Mouvement brouillon supprimé.']);
    }

    public function validateMovement(Request $request, StockMovement $stockMovement): StockMovementResource
    {
        abort_unless($request->user()->can('stock_movements.validate'), 403);

        $movement = $this->stockMovements->validate($stockMovement, $request->user());

        return new StockMovementResource($movement);
    }

    public function cancel(CancelStockMovementRequest $request, StockMovement $stockMovement): StockMovementResource
    {
        $movement = $this->stockMovements->cancel(
            $stockMovement,
            $request->user(),
            $request->validated('cancellation_reason'),
        );

        return new StockMovementResource($movement);
    }
}
