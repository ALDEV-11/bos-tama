<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function paginate(?string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->search($keyword)
            ->withCount('products')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): Category
    {
        return Category::query()->findOrFail($id);
    }

    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = Category::query()->findOrFail($id);
        $category->update($data);

        return $category->refresh();
    }

    public function delete(int $id): bool
    {
        return (bool) Category::query()->whereKey($id)->delete();
    }

    public function isInUse(int $id): bool
    {
        return Product::query()->where('category_id', $id)->exists();
    }

    public function options(): array
    {
        return Category::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}
