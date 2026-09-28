<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Enums\StockMoveType;
use App\Http\Requests\StockMovementRequest;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;

class StockInController extends StockMoveController
{
    public function __construct(
        StockMoveRepositoryInterface $moves,
        ProductRepositoryInterface $products,
        LocationRepositoryInterface $locations,
        private readonly StockService $stock,
    ) {
        parent::__construct($moves, $products, $locations);
    }

    public function store(StockMovementRequest $request): RedirectResponse
    {
        $move = $this->stock->stockIn(
            productId: (int) $request->validated('product_id'),
            locationId: (int) $request->validated('location_id'),
            qty: (int) $request->validated('qty'),
            actor: $request->user(),
            reference: $request->validated('reference'),
            note: $request->validated('note'),
        );

        return $this->success($move);
    }

    protected function type(): StockMoveType
    {
        return StockMoveType::In;
    }

    protected function routePrefix(): string
    {
        return 'stok-masuk';
    }

    protected function pageTitle(): string
    {
        return 'Stok Masuk';
    }
}
