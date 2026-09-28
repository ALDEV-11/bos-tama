<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function paginate(?string $keyword, int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with('role')
            ->when($keyword, fn ($query) => $query->where(function ($inner) use ($keyword) {
                $inner->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            }))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): User
    {
        return User::query()->with('role')->findOrFail($id);
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = User::query()->findOrFail($id);
        $user->fill($data);
        $user->save();

        return $user->refresh();
    }

    public function delete(int $id): bool
    {
        return (bool) User::query()->whereKey($id)->delete();
    }

    public function admins(): Collection
    {
        return User::query()->admins()->active()->get();
    }
}
