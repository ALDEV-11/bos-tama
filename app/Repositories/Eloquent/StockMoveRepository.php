<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Enums\StockMoveType;
use App\Models\LocationStock;
use App\Models\Product;
use App\Models\StockMove;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;

class StockMoveRepository implements StockMoveRepositoryInterface
{
    private const INBOUND_TYPES = [StockMoveType::In, StockMoveType::ReturnIn];

    private const OUTBOUND_TYPES = [StockMoveType::Out, StockMoveType::ReturnOut];

    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->with(['product', 'fromLocation', 'toLocation', 'user'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): StockMove
    {
        return StockMove::query()->create($data);
    }

    public function findOrFail(int $id): StockMove
    {
        return StockMove::query()->with(['product', 'fromLocation', 'toLocation', 'user'])->findOrFail($id);
    }

    public function latest(int $limit = 10): Collection
    {
        return StockMove::query()
            ->with(['product', 'user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function filtered(array $filters): Collection
    {
        return $this->baseQuery($filters)->with('product')->latest()->get();
    }

    public function summary(array $filters): array
    {
        $inQuery = $this->baseQuery($filters)->whereIn('type', array_column(self::INBOUND_TYPES, 'value'));
        $outQuery = $this->baseQuery($filters)->whereIn('type', array_column(self::OUTBOUND_TYPES, 'value'));
        $prices = $this->pricesFor($filters);

        return [
            'in' => (int) $inQuery->sum('qty'),
            'out' => (int) $outQuery->sum('qty'),
            'value_in' => $this->valueOf($inQuery, $prices),
            'value_out' => $this->valueOf($outQuery, $prices),
            'trx' => $this->baseQuery($filters)->count(),
        ];
    }

    public function nextReference(string $prefix): string
    {
        $base = $prefix.'/'.Carbon::now()->format('Y/m');
        $count = StockMove::query()->where('reference', 'like', $base.'/%')->count();

        return $base.'/'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    public function stockAt(int $productId, int $locationId): int
    {
        return (int) LocationStock::query()
            ->where('product_id', $productId)
            ->where('location_id', $locationId)
            ->value('qty');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<StockMove>
     */
    private function baseQuery(array $filters): Builder
    {
        return StockMove::query()
            ->when($filters['search'] ?? null, fn (Builder $q, $keyword) => $q->where(function (Builder $inner) use ($keyword) {
                $inner->where('reference', 'like', "%{$keyword}%")
                    ->orWhereHas('product', fn (Builder $p) => $p->where('name', 'like', "%{$keyword}%"));
            }))
            ->when($filters['type'] ?? null, fn (Builder $q, $type) => $q->whereIn('type', (array) $type))
            ->when($filters['product_id'] ?? null, fn (Builder $q, $productId) => $q->where('product_id', $productId))
            ->when($filters['location_id'] ?? null, fn (Builder $q, $locationId) => $q->where(function (Builder $inner) use ($locationId) {
                $inner->where('from_location_id', $locationId)->orWhere('to_location_id', $locationId);
            }))
            ->when($filters['from'] ?? null, fn (Builder $q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $q, $to) => $q->whereDate('created_at', '<=', $to));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return SupportCollection<int, float>
     */
    private function pricesFor(array $filters): SupportCollection
    {
        $productIds = $this->baseQuery($filters)->distinct()->pluck('product_id');

        return Product::query()->whereIn('id', $productIds)->pluck('price', 'id');
    }

    /**
     * @param  Builder<StockMove>  $query
     * @param  SupportCollection<int, float>  $prices
     */
    private function valueOf(Builder $query, SupportCollection $prices): float
    {
        $total = 0.0;

        foreach ($query->get(['product_id', 'qty']) as $move) {
            $total += (float) ($prices[$move->product_id] ?? 0) * $move->qty;
        }

        return $total;
    }
}
