<x-layouts.app :title="$pageTitle" :active="$routePrefix">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">{{ $pageTitle }}</h1>
            <p class="page-header__subtitle">Riwayat mutasi {{ strtolower($pageTitle) }}.</p>
        </div>

        <div class="page-header__actions">
            <a href="{{ route($routePrefix.'.create') }}" class="btn btn--primary"><i class="bi bi-plus-lg"></i> Tambah</a>
        </div>
    </header>

    <x-ui.card>
        <form method="GET" class="filters">
            <div class="filters__search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari nomor referensi…">
            </div>

            <select name="product_id" class="form-select">
                <option value="">Semua produk</option>
                @foreach ($products as $id => $name)
                    <option value="{{ $id }}" @selected((string) $filters['product_id'] === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>

            <select name="location_id" class="form-select">
                <option value="">Semua lokasi</option>
                @foreach ($locations as $id => $name)
                    <option value="{{ $id }}" @selected((string) $filters['location_id'] === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>

            <label class="filters__date">
                <span>Dari</span>
                <input type="date" name="from" value="{{ $filters['from'] }}" class="form-input">
            </label>

            <label class="filters__date">
                <span>Sampai</span>
                <input type="date" name="to" value="{{ $filters['to'] }}" class="form-input">
            </label>

            <button type="submit" class="btn btn--primary">Terapkan</button>
            <a href="{{ route($routePrefix.'.index') }}" class="btn btn--ghost">Reset</a>
        </form>

        @if ($moves->isEmpty())
            <x-ui.empty title="Belum ada data" description="Catatan {{ strtolower($pageTitle) }} akan tampil di sini." />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Nomor</th>
                        <th>Produk</th>
                        <th>Lokasi</th>
                        <th class="text--right">Jumlah</th>
                        <th class="text--right">Stok</th>
                        <th>Oleh</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($moves as $move)
                        <tr>
                            <td class="text--muted">{{ $move->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td class="mono">{{ $move->reference }}</td>
                            <td>
                                <a href="{{ route('produk.show', $move->product_id) }}">{{ $move->product->name }}</a>
                                <x-ui.badge :variant="$move->type->badge()">{{ $move->typeLabel() }}</x-ui.badge>
                            </td>
                            <td>{{ $move->fromLocation?->name ?? '—' }} &rarr; {{ $move->toLocation?->name ?? '—' }}</td>
                            <td class="text--right mono">{{ $move->qty }}</td>
                            <td class="text--right mono">{{ $move->stock_before }} &rarr; {{ $move->stock_after }}</td>
                            <td>{{ $move->user?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{ $moves->links('pagination::inventori') }}
        @endif
    </x-ui.card>
</x-layouts.app>
