<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockItemResource;
use App\Models\Stock;
use App\Models\StockItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockItemController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('stock_items.view'), 403);

        $companyId = $request->user()->company_id;

        $items = StockItem::query()
            ->with(['product', 'stock'])
            ->whereHas('stock', fn ($query) => $query->where('company_id', $companyId))
            ->when($request->integer('stock_id'), fn ($query, int $stockId) => $query->where('stock_id', $stockId))
            ->when($request->integer('product_id'), fn ($query, int $productId) => $query->where('product_id', $productId))
            ->when($request->boolean('low_stock'), function ($query) {
                $query->join('products', 'products.id', '=', 'stock_items.product_id')
                    ->whereColumn('stock_items.quantity_on_hand', '<', 'products.min_stock_level')
                    ->select('stock_items.*');
            })
            ->orderBy('stock_id')
            ->paginate(50);

        return StockItemResource::collection($items);
    }

    public function byStock(Request $request, Stock $stock): AnonymousResourceCollection
    {
        abort_unless($request->user()->can('stock_items.view'), 403);

        $items = StockItem::query()
            ->with(['product', 'stock'])
            ->where('stock_id', $stock->id)
            ->orderBy('product_id')
            ->paginate(50);

        return StockItemResource::collection($items);
    }
}
