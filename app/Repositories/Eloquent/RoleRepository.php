<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Enums\RoleName;
use App\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function idByName(RoleName $role): ?int
    {
        return Role::query()->where('name', $role->value)->value('id');
    }

    public function options(): array
    {
        return Role::query()
            ->orderBy('id')
            ->pluck('label', 'id')
            ->all();
    }
}
