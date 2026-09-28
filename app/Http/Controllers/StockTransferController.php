<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Enums\StockMoveType;
use App\Http\Requests\StoreTransferRequest;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;

class StockTransferController extends StockMoveController
{
    public function __construct(
        StockMoveRepositoryInterface $moves,
        ProductRepositoryInterface $products,
        LocationRepositoryInterface $locations,
        private readonly StockService $stock,
    ) {
        parent::__construct($moves, $products, $locations);
    }

    public function store(StoreTransferRequest $request): RedirectResponse
    {
        $move = $this->stock->transfer(
            productId: (int) $request->validated('product_id'),
            fromLocationId: (int) $request->validated('from_location_id'),
            toLocationId: (int) $request->validated('to_location_id'),
            qty: (int) $request->validated('qty'),
            actor: $request->user(),
            note: $request->validated('note'),
        );

        return $this->success($move);
    }

    protected function type(): StockMoveType
    {
        return StockMoveType::TransferOut;
    }

    protected function routePrefix(): string
    {
        return 'transfer';
    }

    protected function pageTitle(): string
    {
        return 'Transfer Lokasi';
    }

    protected function variant(): string
    {
        return 'transfer';
    }

    /** @return array<int, string> */
    protected function types(): array
    {
        return [StockMoveType::TransferOut->value, StockMoveType::TransferIn->value];
    }
}
