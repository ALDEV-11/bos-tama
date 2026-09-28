<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SupplierRepositoryInterface;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function paginate(?string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return Supplier::query()
            ->search($keyword)
            ->withCount('products')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Supplier
    {
        return Supplier::query()->findOrFail($id);
    }

    public function create(array $data): Supplier
    {
        return Supplier::query()->create($data);
    }

    public function update(int $id, array $data): Supplier
    {
        $supplier = Supplier::query()->findOrFail($id);
        $supplier->update($data);

        return $supplier->refresh();
    }

    public function delete(int $id): bool
    {
        return (bool) Supplier::query()->whereKey($id)->delete();
    }

    public function isInUse(int $id): bool
    {
        return Product::query()->where('supplier_id', $id)->exists();
    }

    public function options(): array
    {
        return Supplier::query()->active()->orderBy('name')->pluck('name', 'id')->all();
    }
}
