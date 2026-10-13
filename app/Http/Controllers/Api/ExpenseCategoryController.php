<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('expense_categories.view'), 403);

        $categories = ExpenseCategory::query()->orderBy('name')->get();

        return ExpenseCategoryResource::collection($categories);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('expense_categories.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $category = ExpenseCategory::query()->create([
            ...$data,
            'company_id' => $request->user()->company_id,
        ]);

        return (new ExpenseCategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): ExpenseCategoryResource
    {
        abort_unless($request->user()->can('expense_categories.manage'), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $expenseCategory->update($data);

        return new ExpenseCategoryResource($expenseCategory);
    }

    public function destroy(Request $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        abort_unless($request->user()->can('expense_categories.manage'), 403);

        $expenseCategory->update(['is_active' => false]);

        return response()->json(['message' => 'Catégorie désactivée.']);
    }
}
