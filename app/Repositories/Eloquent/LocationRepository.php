<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Models\Location;
use App\Models\LocationStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LocationRepository implements LocationRepositoryInterface
{
    public function paginate(?string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return Location::query()
            ->search($keyword)
            ->withCount('locationStocks')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Location
    {
        return Location::query()->findOrFail($id);
    }

    public function create(array $data): Location
    {
        return Location::query()->create($data);
    }

    public function update(int $id, array $data): Location
    {
        $location = Location::query()->findOrFail($id);
        $location->update($data);

        return $location->refresh();
    }

    public function delete(int $id): bool
    {
        return (bool) Location::query()->whereKey($id)->delete();
    }

    public function options(bool $activeOnly = true): array
    {
        return Location::query()
            ->when($activeOnly, fn ($query) => $query->active())
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Location $location) => [$location->id => "{$location->code} — {$location->name}"])
            ->all();
    }

    public function stockMatrix(): array
    {
        $rows = LocationStock::query()
            ->join('products', 'products.id', '=', 'location_stocks.product_id')
            ->where('products.is_active', true)
            ->where('location_stocks.qty', '>', 0)
            ->orderBy('location_stocks.product_id')
            ->orderBy('location_stocks.location_id')
            ->get(['location_stocks.product_id', 'location_stocks.location_id', 'location_stocks.qty']);

        $matrix = [];

        foreach ($rows as $row) {
            $matrix[$row->product_id][$row->location_id] = (int) $row->qty;
        }

        return $matrix;
    }
}
