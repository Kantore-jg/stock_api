<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('stocks.view'), 403);

        $stocks = Stock::query()->latest()->paginate(20);

        return StockResource::collection($stocks);
    }

    public function store(StoreStockRequest $request): JsonResponse
    {
        $stock = Stock::query()->create([
            ...$request->validated(),
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]);

        $stock->users()->attach($request->user()->id, [
            'assigned_by' => $request->user()->id,
            'can_view' => true,
            'can_manage_stock' => true,
            'can_sell' => true,
            'is_default' => false,
            'assigned_at' => now(),
        ]);

        return (new StockResource($stock))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Stock $stock): StockResource
    {
        abort_unless($request->user()->can('stocks.view'), 403);

        return new StockResource($stock);
    }

    public function update(UpdateStockRequest $request, Stock $stock): StockResource
    {
        $stock->update($request->validated());

        return new StockResource($stock);
    }

    public function destroy(Request $request, Stock $stock): JsonResponse
    {
        abort_unless($request->user()->can('stocks.delete'), 403);

        $stock->delete();

        return response()->json(['message' => 'Stock supprimé.']);
    }
}
