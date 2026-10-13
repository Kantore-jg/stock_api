<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public function log(
        string $action,
        Model $auditable,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $companyId = null,
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::query()->create([
            'company_id' => $companyId
                ?? $user?->company_id
                ?? ($auditable->getAttribute('company_id') ?: null),
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
