<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function show(Request $request): CompanyResource
    {
        abort_unless($request->user()->can('company.view'), 403);

        $company = $request->user()->company()->with('defaultCurrency')->firstOrFail();

        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request): CompanyResource
    {
        $company = $request->user()->company;
        $company->update($request->validated());
        $company->load('defaultCurrency');

        return new CompanyResource($company);
    }
}
