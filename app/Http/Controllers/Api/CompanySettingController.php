<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanySettingRequest;
use App\Http\Resources\CompanySettingResource;
use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanySettingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('settings.view'), 403);

        $settings = CompanySetting::query()->orderBy('setting_key')->get();

        return CompanySettingResource::collection($settings);
    }

    public function upsert(UpdateCompanySettingRequest $request): CompanySettingResource
    {
        $setting = CompanySetting::query()->updateOrCreate(
            [
                'company_id' => $request->user()->company_id,
                'setting_key' => $request->validated('setting_key'),
            ],
            [
                'setting_value' => $request->validated('setting_value'),
            ],
        );

        return new CompanySettingResource($setting);
    }
}
