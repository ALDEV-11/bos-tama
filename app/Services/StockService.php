<?php

namespace App\Services;

use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Enums\StockMoveType;
use App\Exceptions\InsufficientStockException;
use App\Models\Location;
use App\Models\LocationStock;
use App\Models\Product;
use App\Models\StockMove;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockService
{
    public function __construct(
        private readonly StockMoveRepositoryInterface $moves,
        private readonly StockNotifier $notifier,
    ) {}

    public function stockIn(
        int $productId,
        int $locationId,
        int $qty,
        User $actor,
        ?string $reference = null,
        ?string $note = null,
    ): StockMove {
        $move = $this->write(
            productId: $productId,
            locationId: $locationId,
            qty: $qty,
            locationDelta: $qty,
            totalDelta: $qty,
            type: StockMoveType::In,
            fromLocationId: null,
            actor: $actor,
            reference: $reference ?? $this->moves->nextReference('MKM'),
            note: $note,
        );

        $this->notifier->evaluate($move->product, $move->stock_before);

        return $move;
    }

    public function stockOut(
        int $productId,
        int $locationId,
        int $qty,
        User $actor,
        ?string $reference = null,
        ?string $note = null,
    ): StockMove {
        $move = $this->write(
            productId: $productId,
            locationId: $locationId,
            qty: $qty,
            locationDelta: -$qty,
            totalDelta: -$qty,
            type: StockMoveType::Out,
            fromLocationId: $locationId,
            actor: $actor,
            reference: $reference ?? $this->moves->nextReference('KLR'),
            note: $note,
        );

        $this->notifier->evaluate($move->product, $move->stock_before);

        return $move;
    }

    public function returnFromCustomer(
        int $productId,
        int $locationId,
        int $qty,
        User $actor,
        ?string $reference = null,
        ?string $note = null,
    ): StockMove {
        $move = $this->write(
            productId: $productId,
            locationId: $locationId,
            qty: $qty,
            locationDelta: $qty,
            totalDelta: $qty,
            type: StockMoveType::ReturnIn,
            fromLocationId: null,
            actor: $actor,
            reference: $reference ?? $this->moves->nextReference('RTM'),
            note: $note,
        );

        $this->notifier->evaluate($move->product, $move->stock_before);

        return $move;
    }

    public function returnToSupplier(
        int $productId,
        int $locationId,
        int $qty,
        User $actor,
        ?string $reference = null,
        ?string $note = null,
    ): StockMove {
        $move = $this->write(
            productId: $productId,
            locationId: $locationId,
            qty: $qty,
            locationDelta: -$qty,
            totalDelta: -$qty,
            type: StockMoveType::ReturnOut,
            fromLocationId: $locationId,
            actor: $actor,
            reference: $reference ?? $this->moves->nextReference('RTK'),
            note: $note,
        );

        $this->notifier->evaluate($move->product, $move->stock_before);

        return $move;
    }

    public function transfer(
        int $productId,
        int $fromLocationId,
        int $toLocationId,
        int $qty,
        User $actor,
        ?string $note = null,
    ): StockMove {
        if ($fromLocationId === $toLocationId) {
            throw new InvalidArgumentException('Lokasi asal dan tujuan transfer tidak boleh sama.');
        }

        $reference = $this->moves->nextReference('TRF');

        return DB::transaction(function () use ($productId, $fromLocationId, $toLocationId, $qty, $actor, $reference, $note) {
            $out = $this->write(
                productId: $productId,
                locationId: $fromLocationId,
                qty: $qty,
                locationDelta: -$qty,
                totalDelta: 0,
                type: StockMoveType::TransferOut,
                fromLocationId: $fromLocationId,
                actor: $actor,
                reference: $reference,
                note: $note,
            );

            $in = $this->write(
                productId: $productId,
                locationId: $toLocationId,
                qty: $qty,
                locationDelta: $qty,
                totalDelta: 0,
                type: StockMoveType::TransferIn,
                fromLocationId: null,
                actor: $actor,
                reference: $reference,
                note: $note,
            );

            $out->update(['related_move_id' => $in->id]);

            return $in;
        });
    }

    public function adjust(
        int $productId,
        int $locationId,
        int $physicalQty,
        User $actor,
        ?string $note = null,
    ): StockMove {
        $delta = $physicalQty - $this->moves->stockAt($productId, $locationId);

        $move = $this->write(
            productId: $productId,
            locationId: $locationId,
            qty: abs($delta),
            locationDelta: $delta,
            totalDelta: $delta,
            type: StockMoveType::Adjustment,
            fromLocationId: $delta >= 0 ? null : $locationId,
            actor: $actor,
            reference: $this->moves->nextReference('PNY'),
            note: $note ?? "Hasil opname fisik: {$physicalQty}",
        );

        $this->notifier->evaluate($move->product, $move->stock_before);

        return $move;
    }

    private function write(
        int $productId,
        int $locationId,
        int $qty,
        int $locationDelta,
        int $totalDelta,
        StockMoveType $type,
        ?int $fromLocationId,
        User $actor,
        ?string $reference,
        ?string $note,
    ): StockMove {
        return DB::transaction(function () use ($productId, $locationId, $qty, $locationDelta, $totalDelta, $type, $fromLocationId, $actor, $reference, $note) {
            $product = Product::query()->whereKey($productId)->lockForUpdate()->firstOrFail();
            $locationName = Location::query()->whereKey($locationId)->value('name') ?? "lokasi #{$locationId}";

            $locationStock = LocationStock::query()
                ->where('product_id', $productId)
                ->where('location_id', $locationId)
                ->lockForUpdate()
                ->first() ?? LocationStock::query()->create([
                    'product_id' => $productId,
                    'location_id' => $locationId,
                    'qty' => 0,
                ]);

            $remaining = $locationStock->qty + $locationDelta;

            if ($remaining < 0) {
                throw new InsufficientStockException($product->name, $locationName, $qty, $locationStock->qty);
            }

            $locationStock->update(['qty' => $remaining]);

            $stockBefore = $product->stock;
            $product->update(['stock' => $stockBefore + $totalDelta]);

            return $this->moves->create([
                'product_id' => $product->id,
                'type' => $type,
                'qty' => $qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockBefore + $totalDelta,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $fromLocationId === null ? $locationId : null,
                'reference' => $reference,
                'note' => $note,
                'user_id' => $actor->id,
            ]);
        })->load('product');
    }
}
