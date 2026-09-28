<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

interface NotificationRepositoryInterface
{
    public function unreadCount(int $userId): int;

    /** @return Collection<int, DatabaseNotification> */
    public function latestFor(int $userId, int $limit = 8): Collection;

    public function paginateFor(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function markAsRead(string $id, int $userId): bool;

    public function markAllAsRead(int $userId): int;

    public function clear(int $userId): int;
}
