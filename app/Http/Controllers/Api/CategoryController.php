<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('categories.view'), 403);

        $categories = Category::query()
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->paginate(50);

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::query()->create([
            ...$request->validated(),
            'company_id' => $request->user()->company_id,
        ]);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Category $category): CategoryResource
    {
        abort_unless($request->user()->can('categories.view'), 403);

        $category->load('children');

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        abort_unless($request->user()->can('categories.delete'), 403);

        $category->delete();

        return response()->json(['message' => 'Catégorie supprimée.']);
    }
}
