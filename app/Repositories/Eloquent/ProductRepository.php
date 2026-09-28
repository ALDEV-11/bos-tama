<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Enums\StockStatus;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::query()->with('category');

        $this->applyFilters($query, $filters);

        return $query
            ->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): ?Product
    {
        return Product::query()->with(['category', 'supplier'])->find($id);
    }

    public function findOrFail(int $id): Product
    {
        return Product::query()->with(['category', 'supplier', 'locationStocks.location'])->findOrFail($id);
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::query()->where('sku', $sku)->first();
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = Product::query()->findOrFail($id);
        $product->update($data);

        return $product->refresh();
    }

    public function delete(int $id): bool
    {
        return (bool) Product::query()->whereKey($id)->delete();
    }

    public function hasStock(int $id): bool
    {
        return Product::query()
            ->whereKey($id)
            ->where('stock', '>', 0)
            ->exists();
    }

    public function markAlerted(int $id): Product
    {
        $product = Product::query()->findOrFail($id);
        $product->update(['last_alerted_at' => now()]);

        return $product;
    }

    public function allActive(): Collection
    {
        return Product::query()->active()->orderBy('name')->get();
    }

    public function needsAttention(int $limit = 5): Collection
    {
        return Product::query()
            ->active()
            ->whereColumn('stock', '<=', 'min_stock')
            ->with('category')
            ->orderBy('stock')
            ->limit($limit)
            ->get();
    }

    public function options(): array
    {
        return Product::query()
            ->active()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function count(): int
    {
        return Product::query()->count();
    }

    public function totalStock(): int
    {
        return (int) Product::query()->sum('stock');
    }

    public function totalStockValue(): float
    {
        return (float) Product::query()->sum(DB::raw('stock * price'));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  Builder<Product>  $query
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->search($filters['search'] ?? null)
            ->when($filters['category_id'] ?? null, fn (Builder $q, $categoryId) => $q->where('category_id', $categoryId))
            ->when(($filters['status'] ?? '') !== '', function (Builder $q) use ($filters) {
                match ($filters['status']) {
                    StockStatus::Out->value => $q->where('stock', 0),
                    StockStatus::Low->value => $q->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock'),
                    StockStatus::Safe->value => $q->whereColumn('stock', '>', 'min_stock'),
                    default => $q,
                };
            })
            ->when(($filters['is_active'] ?? '') !== '', fn (Builder $q, $value) => $q->where('is_active', (bool) $value));
    }
}
