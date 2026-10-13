<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('invoices.view'), 403);

        $invoices = Invoice::query()
            ->with(['currency', 'sale'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('invoice_date')
            ->paginate(20);

        return InvoiceResource::collection($invoices);
    }

    public function show(Request $request, Invoice $invoice): InvoiceResource
    {
        abort_unless($request->user()->can('invoices.view'), 403);

        $invoice->load(['currency', 'sale.items', 'sale.customer', 'sale.payments']);

        return new InvoiceResource($invoice);
    }
}
