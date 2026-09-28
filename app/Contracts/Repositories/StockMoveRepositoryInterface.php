<?php

namespace App\Contracts\Repositories;

use App\Models\StockMove;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface StockMoveRepositoryInterface
{
    /** @param array<string, mixed> $filters */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator;

    /** @param array<string, mixed> $data */
    public function create(array $data): StockMove;

    public function findOrFail(int $id): StockMove;

    /** @return Collection<int, StockMove> */
    public function latest(int $limit = 10): Collection;

    /** @param array<string, mixed> $filters */
    public function filtered(array $filters): Collection;

    /** @param array<string, mixed> $filters */
    public function summary(array $filters): array;

    public function nextReference(string $prefix): string;

    public function stockAt(int $productId, int $locationId): int;
}
