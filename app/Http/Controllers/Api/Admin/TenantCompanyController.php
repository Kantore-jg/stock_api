<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Models\Company;
use App\Services\CompanyProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TenantCompanyController extends Controller
{
    public function __construct(
        private readonly CompanyProvisioningService $provisioning,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $companies = Company::query()
            ->with('defaultCurrency')
            ->withCount('users')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        return CompanyResource::collection($companies);
    }

    public function store(StoreTenantCompanyRequest $request): JsonResponse
    {
        $result = $this->provisioning->provision($request->validated());

        return response()->json([
            'message' => 'Entreprise créée.',
            'company' => new CompanyResource($result['company']),
            'admin' => new UserResource($result['admin']),
        ], 201);
    }

    public function show(Company $company): CompanyResource
    {
        $company->load('defaultCurrency')->loadCount('users');

        return new CompanyResource($company);
    }

    public function update(Request $request, Company $company): CompanyResource
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:180'],
            'legal_name' => ['nullable', 'string', 'max:200'],
            'tax_number' => ['nullable', 'string', 'max:80'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $company->update($data);

        return new CompanyResource($company->fresh('defaultCurrency')->loadCount('users'));
    }
}
