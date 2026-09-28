<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationRepositoryInterface $notifications) {}

    public function index(): View
    {
        return view('notifikasi.index', [
            'notifications' => $this->notifications->paginateFor((int) auth()->id()),
        ]);
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $updated = $this->notifications->markAsRead($notification, (int) $request->user()->id);

        $target = $updated ? route('produk.index') : route('notifikasi.index');

        return redirect()->to($target);
    }

    public function readAll(): RedirectResponse
    {
        $count = $this->notifications->markAllAsRead((int) auth()->id());

        return redirect()->route('notifikasi.index')->with('success', "{$count} notifikasi ditandai sudah dibaca.");
    }

    public function clear(): RedirectResponse
    {
        $count = $this->notifications->clear((int) auth()->id());

        return redirect()->route('notifikasi.index')->with('success', "{$count} notifikasi dihapus.");
    }
}
