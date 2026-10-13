<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UnitController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('units.view'), 403);

        $units = Unit::query()->orderBy('name')->paginate(50);

        return UnitResource::collection($units);
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        $unit = Unit::query()->create([
            ...$request->validated(),
            'company_id' => $request->user()->company_id,
        ]);

        return (new UnitResource($unit))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Unit $unit): UnitResource
    {
        abort_unless($request->user()->can('units.view'), 403);

        return new UnitResource($unit);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): UnitResource
    {
        $unit->update($request->validated());

        return new UnitResource($unit);
    }

    public function destroy(Request $request, Unit $unit): JsonResponse
    {
        abort_unless($request->user()->can('units.delete'), 403);

        $unit->delete();

        return response()->json(['message' => 'Unité supprimée.']);
    }
}
