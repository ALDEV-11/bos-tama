<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Enums\StockMoveType;
use App\Http\Requests\StoreAdjustmentRequest;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;

class StockAdjustmentController extends StockMoveController
{
    public function __construct(
        StockMoveRepositoryInterface $moves,
        ProductRepositoryInterface $products,
        LocationRepositoryInterface $locations,
        private readonly StockService $stock,
    ) {
        parent::__construct($moves, $products, $locations);
    }

    public function store(StoreAdjustmentRequest $request): RedirectResponse
    {
        $move = $this->stock->adjust(
            productId: (int) $request->validated('product_id'),
            locationId: (int) $request->validated('location_id'),
            physicalQty: (int) $request->validated('physical_qty'),
            actor: $request->user(),
            note: $request->validated('note'),
        );

        return $this->success($move);
    }

    protected function type(): StockMoveType
    {
        return StockMoveType::Adjustment;
    }

    protected function routePrefix(): string
    {
        return 'opname';
    }

    protected function pageTitle(): string
    {
        return 'Opname Stok';
    }

    protected function variant(): string
    {
        return 'adjustment';
    }
}
