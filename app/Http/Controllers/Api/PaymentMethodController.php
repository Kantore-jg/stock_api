<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentMethodController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('payment_methods.view'), 403);

        $methods = PaymentMethod::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return PaymentMethodResource::collection($methods);
    }
}
