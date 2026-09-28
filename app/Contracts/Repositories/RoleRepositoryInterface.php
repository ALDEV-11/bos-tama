<?php

namespace App\Contracts\Repositories;

use App\Enums\RoleName;

interface RoleRepositoryInterface
{
    public function idByName(RoleName $role): ?int;

    /** @return array<int, string> daftar id => label untuk dropdown */
    public function options(): array;
}
