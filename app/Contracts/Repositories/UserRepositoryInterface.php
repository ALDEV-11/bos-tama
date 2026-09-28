<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function paginate(?string $keyword, int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): User;

    /** @param array<string, mixed> $data */
    public function create(array $data): User;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): User;

    public function delete(int $id): bool;

    /** @return Collection<int, User> penerima notifikasi stok menipis */
    public function admins(): Collection;
}
