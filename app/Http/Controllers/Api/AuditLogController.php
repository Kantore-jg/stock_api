<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('audit_logs.view'), 403);

        $logs = AuditLog::query()
            ->with('user')
            ->where('company_id', $request->user()->company_id)
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->when($request->filled('auditable_type'), fn ($query) => $query->where('auditable_type', $request->string('auditable_type')))
            ->latest('created_at')
            ->paginate(50);

        return AuditLogResource::collection($logs);
    }
}
