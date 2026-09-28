<?php

namespace App\View\Composers;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SidebarComposer
{
    public function __construct(private readonly NotificationRepositoryInterface $notifications) {}

    public function compose(View $view): void
    {
        $user = Auth::user();

        if ($user === null) {
            $view->with(['unreadCount' => 0, 'latestNotifications' => collect()]);

            return;
        }

        $view->with([
            'unreadCount' => $this->notifications->unreadCount($user->id),
            'latestNotifications' => $this->notifications->latestFor($user->id),
        ]);
    }
}
