@php($user = auth()->user())

<nav class="sidebar__nav" aria-label="Menu utama">
    <p class="sidebar__section">Utama</p>
    <x-nav-link route="dashboard" icon="bi-speedometer2" :active="$active === 'dashboard'">Dashboard</x-nav-link>
    <x-nav-link route="produk.index" icon="bi-box-seam" :active="$active === 'produk'">Produk</x-nav-link>
    <x-nav-link route="notifikasi.index" icon="bi-bell" :active="$active === 'notifikasi'" :badge="$unreadCount ?? 0">Notifikasi</x-nav-link>

    <p class="sidebar__section">Transaksi Stok</p>
    <x-nav-link route="stok-masuk.index" icon="bi-box-arrow-in-down" :active="$active === 'stok-masuk'">Stok Masuk</x-nav-link>
    <x-nav-link route="stok-keluar.index" icon="bi-box-arrow-up" :active="$active === 'stok-keluar'">Stok Keluar</x-nav-link>
    <x-nav-link route="transfer.index" icon="bi-arrow-left-right" :active="$active === 'transfer'">Transfer Lokasi</x-nav-link>
    <x-nav-link route="retur.index" icon="bi-arrow-return-left" :active="$active === 'retur'">Retur</x-nav-link>

    <p class="sidebar__section">Laporan</p>
    <x-nav-link route="laporan.index" icon="bi-journal-text" :active="$active === 'laporan'">Mutasi Stok</x-nav-link>
    <x-nav-link route="persediaan.index" icon="bi-clipboard-data" :active="$active === 'persediaan'">Persediaan</x-nav-link>

    @if ($user?->isAdmin())
        <p class="sidebar__section">Administrasi</p>
        <x-nav-link route="opname.index" icon="bi-clipboard-check" :active="$active === 'opname'">Opname Stok</x-nav-link>
        <x-nav-link route="kategori.index" icon="bi-tags" :active="$active === 'kategori'">Kategori</x-nav-link>
        <x-nav-link route="supplier.index" icon="bi-truck" :active="$active === 'supplier'">Supplier</x-nav-link>
        <x-nav-link route="lokasi.index" icon="bi-geo-alt" :active="$active === 'lokasi'">Lokasi</x-nav-link>
        <x-nav-link route="pengguna.index" icon="bi-people" :active="$active === 'pengguna'">Pengguna</x-nav-link>
    @endif
</nav>
