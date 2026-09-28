<?php

namespace App\Contracts\Repositories;

interface DashboardRepositoryInterface
{
    /** @return array<string, int|float> */
    public function summary(): array;

    /** @return array<string, array{label: string, in: int, out: int}> stok masuk & keluar per hari */
    public function dailyMovement(int $days = 7): array;
}
