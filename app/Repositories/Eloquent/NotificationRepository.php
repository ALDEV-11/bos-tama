<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function unreadCount(int $userId): int
    {
        return $this->query($userId)->whereNull('read_at')->count();
    }

    public function latestFor(int $userId, int $limit = 8): Collection
    {
        return $this->query($userId)->latest()->limit($limit)->get();
    }

    public function paginateFor(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query($userId)->latest()->paginate($perPage);
    }

    public function markAsRead(string $id, int $userId): bool
    {
        return (bool) $this->query($userId)->whereKey($id)->update(['read_at' => now()]);
    }

    public function markAllAsRead(int $userId): int
    {
        return $this->query($userId)->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function clear(int $userId): int
    {
        return $this->query($userId)->delete();
    }

    /** @return Builder<DatabaseNotification> */
    private function query(int $userId): Builder
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $userId);
    }
}
