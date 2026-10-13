<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\SaleResource;
use App\Models\Payment;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        private readonly SaleService $sales,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('payments.view'), 403);

        $payments = Payment::query()
            ->with(['paymentMethod', 'currency'])
            ->when($request->integer('sale_id'), fn ($query, int $saleId) => $query->where('sale_id', $saleId))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return PaymentResource::collection($payments);
    }

    public function store(StorePaymentRequest $request, Sale $sale): JsonResponse
    {
        $payment = $this->sales->addPayment($sale, $request->user(), $request->validated());
        $sale->refresh()->load(['payments.paymentMethod', 'invoice', 'customer']);

        return response()->json([
            'payment' => new PaymentResource($payment),
            'sale' => new SaleResource($sale),
        ], 201);
    }
}
