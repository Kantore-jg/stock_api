<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CurrencyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('currencies.view'), 403);

        $currencies = Currency::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return CurrencyResource::collection($currencies);
    }

    public function show(Request $request, Currency $currency): CurrencyResource
    {
        abort_unless($request->user()->can('currencies.view'), 403);

        return new CurrencyResource($currency);
    }
}
