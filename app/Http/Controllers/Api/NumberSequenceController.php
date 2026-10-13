<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNumberSequenceRequest;
use App\Http\Requests\UpdateNumberSequenceRequest;
use App\Http\Resources\NumberSequenceResource;
use App\Models\NumberSequence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NumberSequenceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('settings.view'), 403);

        $sequences = NumberSequence::query()->orderBy('document_type')->get();

        return NumberSequenceResource::collection($sequences);
    }

    public function store(StoreNumberSequenceRequest $request): JsonResponse
    {
        $sequence = NumberSequence::query()->create([
            ...$request->validated(),
            'company_id' => $request->user()->company_id,
        ]);

        return (new NumberSequenceResource($sequence))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateNumberSequenceRequest $request, NumberSequence $numberSequence): NumberSequenceResource
    {
        $numberSequence->update($request->validated());

        return new NumberSequenceResource($numberSequence);
    }

    public function destroy(Request $request, NumberSequence $numberSequence): JsonResponse
    {
        abort_unless($request->user()->can('settings.update'), 403);

        $numberSequence->delete();

        return response()->json(['message' => 'Séquence supprimée.']);
    }
}
