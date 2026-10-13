<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'stocks' => StockResource::collection($this->whenLoaded('stocks')),
            'roles' => $this->when(
                method_exists($this->resource, 'getRoleNames'),
                fn () => $this->getRoleNames()->values(),
            ),
            'permissions' => $this->when(
                method_exists($this->resource, 'getAllPermissions'),
                fn () => $this->getAllPermissions()->pluck('name')->values(),
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
