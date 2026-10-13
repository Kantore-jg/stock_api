<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignStockUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockUserController extends Controller
{
    public function index(Request $request, Stock $stock): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('stocks.view'), 403);

        $users = $stock->users()->get();

        return UserResource::collection($users);
    }

    public function store(AssignStockUserRequest $request, Stock $stock): JsonResponse
    {
        $data = $request->validated();

        if (($data['is_default'] ?? false) === true) {
            $stock->users()->newPivotQuery()->update(['is_default' => false]);
        }

        $stock->users()->syncWithoutDetaching([
            $data['user_id'] => [
                'assigned_by' => $request->user()->id,
                'can_view' => $data['can_view'] ?? true,
                'can_manage_stock' => $data['can_manage_stock'] ?? false,
                'can_sell' => $data['can_sell'] ?? false,
                'is_default' => $data['is_default'] ?? false,
                'assigned_at' => now(),
            ],
        ]);

        $user = User::query()->findOrFail($data['user_id']);
        $user->load(['stocks' => fn ($query) => $query->where('stocks.id', $stock->id)]);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function update(AssignStockUserRequest $request, Stock $stock, User $user): UserResource
    {
        abort_unless($stock->users()->where('users.id', $user->id)->exists(), 404);

        $data = $request->validated();

        if (($data['is_default'] ?? false) === true) {
            $stock->users()->newPivotQuery()->update(['is_default' => false]);
        }

        $stock->users()->updateExistingPivot($user->id, [
            'can_view' => $data['can_view'] ?? true,
            'can_manage_stock' => $data['can_manage_stock'] ?? false,
            'can_sell' => $data['can_sell'] ?? false,
            'is_default' => $data['is_default'] ?? false,
        ]);

        $user->load(['stocks' => fn ($query) => $query->where('stocks.id', $stock->id)]);

        return new UserResource($user);
    }

    public function destroy(Request $request, Stock $stock, User $user): JsonResponse
    {
        abort_unless($request->user()->can('stocks.assign_users'), 403);

        $stock->users()->detach($user->id);

        return response()->json(['message' => 'Utilisateur retiré du stock.']);
    }
}
