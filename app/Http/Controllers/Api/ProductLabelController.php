<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductLabelRequest;
use App\Http\Requests\UpdateProductLabelRequest;
use App\Http\Resources\ProductLabelResource;
use App\Models\ProductLabel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductLabelController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('product_labels.view'), 403);

        $labels = ProductLabel::query()->orderBy('name')->paginate(50);

        return ProductLabelResource::collection($labels);
    }

    public function store(StoreProductLabelRequest $request): JsonResponse
    {
        $label = ProductLabel::query()->create([
            ...$request->validated(),
            'company_id' => $request->user()->company_id,
        ]);

        return (new ProductLabelResource($label))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, ProductLabel $productLabel): ProductLabelResource
    {
        abort_unless($request->user()->can('product_labels.view'), 403);

        return new ProductLabelResource($productLabel);
    }

    public function update(UpdateProductLabelRequest $request, ProductLabel $productLabel): ProductLabelResource
    {
        $productLabel->update($request->validated());

        return new ProductLabelResource($productLabel);
    }

    public function destroy(Request $request, ProductLabel $productLabel): JsonResponse
    {
        abort_unless($request->user()->can('product_labels.delete'), 403);

        $productLabel->delete();

        return response()->json(['message' => 'Libellé supprimé.']);
    }
}
