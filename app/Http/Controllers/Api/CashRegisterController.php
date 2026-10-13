<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CashRegisterResource;
use App\Models\CashRegister;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CashRegisterController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('cash_registers.view'), 403);

        $registers = CashRegister::query()
            ->with(['stock', 'currency'])
            ->when($request->integer('stock_id'), fn ($query, int $stockId) => $query->where('stock_id', $stockId))
            ->orderBy('name')
            ->paginate(20);

        return CashRegisterResource::collection($registers);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('cash_registers.create'), 403);

        $companyId = $request->user()->company_id;
        $data = $request->validate([
            'stock_id' => [
                'required',
                'integer',
                Rule::exists('stocks', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'name' => ['required', 'string', 'max:100'],
            'currency_id' => ['required', 'integer', Rule::exists('currencies', 'id')],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $register = CashRegister::query()->create([
            ...$data,
            'company_id' => $companyId,
        ]);

        return (new CashRegisterResource($register->load(['stock', 'currency'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, CashRegister $cashRegister): CashRegisterResource
    {
        abort_unless($request->user()->can('cash_registers.view'), 403);

        return new CashRegisterResource($cashRegister->load(['stock', 'currency']));
    }

    public function update(Request $request, CashRegister $cashRegister): CashRegisterResource
    {
        abort_unless($request->user()->can('cash_registers.update'), 403);

        $companyId = $request->user()->company_id;
        $data = $request->validate([
            'stock_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('stocks', 'id')->where(fn ($query) => $query->where('company_id', $companyId)->whereNull('deleted_at')),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'currency_id' => ['sometimes', 'required', 'integer', Rule::exists('currencies', 'id')],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $cashRegister->update($data);

        return new CashRegisterResource($cashRegister->fresh(['stock', 'currency']));
    }

    public function destroy(Request $request, CashRegister $cashRegister): JsonResponse
    {
        abort_unless($request->user()->can('cash_registers.update'), 403);

        $cashRegister->update(['is_active' => false]);

        return response()->json(['message' => 'Caisse désactivée.']);
    }
}
