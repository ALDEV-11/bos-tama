<x-layouts.app title="Notifikasi" active="notifikasi">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">Notifikasi</h1>
            <p class="page-header__subtitle">Peringatan stok menipis dan stok habis.</p>
        </div>

        <div class="page-header__actions">
            <form method="POST" action="{{ route('notifikasi.readAll') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn--outline">Tandai semua dibaca</button>
            </form>

            <form method="POST" action="{{ route('notifikasi.clear') }}" data-confirm-form data-confirm="Hapus semua notifikasi? Tindakan ini tidak dapat dibatalkan.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--danger">Hapus semua</button>
            </form>
        </div>
    </header>

    <x-ui.card>
        @forelse ($notifications as $notification)
            <article class="notification-row {{ $notification->read_at ? '' : 'notification-row--unread' }}">
                <i class="bi {{ $notification->data['icon'] ?? 'bi-bell' }} notification-row__icon"></i>

                <div class="notification-row__body">
                    <h3>{{ $notification->data['title'] ?? 'Notifikasi' }}</h3>
                    <p>{{ $notification->data['message'] ?? '' }}</p>
                    <small class="text--muted">@timeago($notification->created_at)</small>
                </div>

                <div class="notification-row__actions">
                    @if (($notification->data['url'] ?? null) && $notification->data['product_id'])
                        <a href="{{ route('produk.show', $notification->data['product_id']) }}" class="btn btn--ghost btn--sm">Lihat produk</a>
                    @endif

                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('notifikasi.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn--outline btn--sm">Tandai dibaca</button>
                        </form>
                    @endunless
                </div>
            </article>
        @empty
            <x-ui.empty icon="bi-bell-slash" title="Belum ada notifikasi" description="Notifikasi muncul otomatis ketika stok menyentuh batas minimum." />
        @endforelse

        {{ $notifications->links('pagination::inventori') }}
    </x-ui.card>
</x-layouts.app>
