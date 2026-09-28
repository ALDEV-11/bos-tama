<?php

namespace App\Contracts\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /** @param array<string, mixed> $filters */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function find(int $id): ?Product;

    public function findOrFail(int $id): Product;

    public function findBySku(string $sku): ?Product;

    /** @param array<string, mixed> $data */
    public function create(array $data): Product;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Product;

    public function delete(int $id): bool;

    /** Gambaran apakah produk masih memiliki stok sehingga tidak boleh dihapus. */
    public function hasStock(int $id): bool;

    /** Tandai waktu notifikasi batas stok terakhir agar tidak dikirim berulang. */
    public function markAlerted(int $id): Product;

    /** @return Collection<int, Product> */
    public function allActive(): Collection;

    /** @return Collection<int, Product> */
    public function needsAttention(int $limit = 5): Collection;

    /** @return array<int, string> daftar id => nama untuk dropdown */
    public function options(): array;

    public function count(): int;

    public function totalStock(): int;

    public function totalStockValue(): float;
}
