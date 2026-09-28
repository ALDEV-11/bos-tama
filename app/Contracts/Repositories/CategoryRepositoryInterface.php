<?php

namespace App\Contracts\Repositories;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    public function paginate(?string $keyword, int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Category;

    /** @param array<string, mixed> $data */
    public function create(array $data): Category;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Category;

    public function delete(int $id): bool;

    public function isInUse(int $id): bool;

    /** @return array<int, string> */
    public function options(): array;
}
