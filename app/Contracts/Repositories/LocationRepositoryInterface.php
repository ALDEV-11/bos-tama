<?php

namespace App\Contracts\Repositories;

use App\Models\Location;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LocationRepositoryInterface
{
    public function paginate(?string $keyword, int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Location;

    /** @param array<string, mixed> $data */
    public function create(array $data): Location;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Location;

    public function delete(int $id): bool;

    /** @return array<int, string> */
    public function options(bool $activeOnly = true): array;

    /**
     * Sisa stok produk aktif di setiap lokasi, untuk form mutasi.
     *
     * @return array<int, array<int, int>> id produk => id lokasi => jumlah
     */
    public function stockMatrix(): array;
}
