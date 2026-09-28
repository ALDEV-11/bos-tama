<?php

namespace App\Contracts\Repositories;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SupplierRepositoryInterface
{
    public function paginate(?string $keyword, int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Supplier;

    /** @param array<string, mixed> $data */
    public function create(array $data): Supplier;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Supplier;

    public function delete(int $id): bool;

    public function isInUse(int $id): bool;

    /** @return array<int, string> */
    public function options(): array;
}
