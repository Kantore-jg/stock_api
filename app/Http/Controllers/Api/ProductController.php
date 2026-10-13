<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('products.view'), 403);

        $products = Product::query()
            ->with(['category', 'productLabel', 'unit', 'currency'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int) $request->integer('per_page', 20));

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::query()->create([
            ...$request->validated(),
            'company_id' => $request->user()->company_id,
            'created_by' => $request->user()->id,
        ]);

        $product->load(['category', 'productLabel', 'unit', 'currency']);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Product $product): ProductResource
    {
        abort_unless($request->user()->can('products.view'), 403);

        $product->load(['category', 'productLabel', 'unit', 'currency']);

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());
        $product->load(['category', 'productLabel', 'unit', 'currency']);

        return new ProductResource($product);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        abort_unless($request->user()->can('products.delete'), 403);

        $product->delete();

        return response()->json(['message' => 'Produit supprimé.']);
    }
}
