<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('customers.view'), 403);

        $customers = Customer::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return CustomerResource::collection($customers);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::query()->create([
            ...$request->validated(),
            'company_id' => $request->user()->company_id,
        ]);

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Customer $customer): CustomerResource
    {
        abort_unless($request->user()->can('customers.view'), 403);

        return new CustomerResource($customer);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        $customer->update($request->validated());

        return new CustomerResource($customer);
    }

    public function destroy(Request $request, Customer $customer): JsonResponse
    {
        abort_unless($request->user()->can('customers.delete'), 403);

        $customer->delete();

        return response()->json(['message' => 'Client supprimé.']);
    }
}
