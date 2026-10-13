<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelSaleRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Http\Resources\SaleResource;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService $sales,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('sales.view'), 403);

        $sales = Sale::query()
            ->with(['customer', 'stock', 'currency'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->integer('stock_id'), fn ($query, int $stockId) => $query->where('stock_id', $stockId))
            ->when($request->integer('customer_id'), fn ($query, int $customerId) => $query->where('customer_id', $customerId))
            ->latest('sale_date')
            ->paginate(20);

        return SaleResource::collection($sales);
    }

    public function store(StoreSaleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        $sale = $this->sales->createDraft($request->user(), $validated, $items);

        return (new SaleResource($sale))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Sale $sale): SaleResource
    {
        abort_unless($request->user()->can('sales.view'), 403);

        $sale->load(['items.product', 'customer', 'stock', 'currency', 'invoice', 'payments.paymentMethod']);

        return new SaleResource($sale);
    }

    public function update(UpdateSaleRequest $request, Sale $sale): SaleResource
    {
        $validated = $request->validated();
        $items = $validated['items'] ?? null;
        unset($validated['items']);

        $sale = $this->sales->updateDraft($sale, $validated, $items);

        return new SaleResource($sale);
    }

    public function destroy(Request $request, Sale $sale): JsonResponse
    {
        abort_unless($request->user()->can('sales.update'), 403);
        abort_unless($sale->isDraft(), 422, 'Seul un brouillon peut être supprimé.');

        $sale->items()->delete();
        $sale->delete();

        return response()->json(['message' => 'Vente brouillon supprimée.']);
    }

    public function validateSale(Request $request, Sale $sale): SaleResource
    {
        abort_unless($request->user()->can('sales.validate'), 403);

        $sale = $this->sales->validate($sale, $request->user());

        return new SaleResource($sale);
    }

    public function cancel(CancelSaleRequest $request, Sale $sale): SaleResource
    {
        $sale = $this->sales->cancel(
            $sale,
            $request->user(),
            $request->validated('cancellation_reason'),
        );

        return new SaleResource($sale);
    }
}
