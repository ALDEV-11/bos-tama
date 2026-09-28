<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Enums\StockMoveType;
use App\Http\Requests\StoreReturnRequest;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;

class StockReturnController extends StockMoveController
{
    public function __construct(
        StockMoveRepositoryInterface $moves,
        ProductRepositoryInterface $products,
        LocationRepositoryInterface $locations,
        private readonly StockService $stock,
    ) {
        parent::__construct($moves, $products, $locations);
    }

    public function store(StoreReturnRequest $request): RedirectResponse
    {
        $payload = [
            'productId' => (int) $request->validated('product_id'),
            'locationId' => (int) $request->validated('location_id'),
            'qty' => (int) $request->validated('qty'),
            'actor' => $request->user(),
            'reference' => $request->validated('reference'),
            'note' => $request->validated('note'),
        ];

        $move = $request->validated('direction') === 'retur_masuk'
            ? $this->stock->returnFromCustomer(...$payload)
            : $this->stock->returnToSupplier(...$payload);

        return $this->success($move);
    }

    protected function type(): StockMoveType
    {
        return StockMoveType::ReturnOut;
    }

    protected function routePrefix(): string
    {
        return 'retur';
    }

    protected function pageTitle(): string
    {
        return 'Retur';
    }

    protected function variant(): string
    {
        return 'return';
    }

    /** @return array<int, string> */
    protected function types(): array
    {
        return [StockMoveType::ReturnIn->value, StockMoveType::ReturnOut->value];
    }
}
