<?php

namespace App\Http\Resources;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CompanySetting
 */
class CompanySettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'setting_key' => $this->setting_key,
            'setting_value' => $this->setting_value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
