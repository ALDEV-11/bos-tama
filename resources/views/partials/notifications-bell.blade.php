<div class="notification" data-dropdown>
    <button type="button" class="notification__button" data-dropdown-toggle aria-expanded="false" aria-label="Notifikasi">
        <i class="bi bi-bell"></i>
        @if (($unreadCount ?? 0) > 0)
            <span class="notification__badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div class="notification__panel" data-dropdown-panel>
        <div class="notification__head">
            <strong>Notifikasi Stok</strong>
            <form method="POST" action="{{ route('notifikasi.readAll') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn--link btn--sm">Tandai semua dibaca</button>
            </form>
        </div>

        <div class="notification__list">
            @forelse ($latestNotifications as $notification)
                <a href="{{ route('notifikasi.read', $notification->id) }}"
                   class="notification__item {{ $notification->read_at ? '' : 'notification__item--unread' }}">
                    <i class="bi {{ $notification->data['icon'] ?? 'bi-bell' }}"></i>
                    <div>
                        <strong>{{ $notification->data['title'] ?? 'Notifikasi' }}</strong>
                        <p>{{ $notification->data['message'] ?? '' }}</p>
                        <small>@timeago($notification->created_at)</small>
                    </div>
                </a>
            @empty
                <p class="notification__empty">Belum ada notifikasi.</p>
            @endforelse
        </div>

        <a href="{{ route('notifikasi.index') }}" class="notification__footer">Lihat semua notifikasi</a>
    </div>
</div>
