<x-layouts.app title="Laporan Mutasi Stok" active="laporan">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">Laporan Mutasi Stok</h1>
            <p class="page-header__subtitle">Rekap seluruh pergerakan stok dengan penyaring tanggal, tipe, dan lokasi.</p>
        </div>

        <div class="page-header__actions">
            <a href="{{ route('laporan.cetak', $filters) }}" target="_blank" rel="noopener" class="btn btn--outline"><i class="bi bi-printer"></i> Cetak</a>
            <a href="{{ route('laporan.unduh', $filters) }}" class="btn btn--primary"><i class="bi bi-download"></i> Unduh CSV</a>
        </div>
    </header>

    <div class="stat-grid stat-grid--compact">
        <x-ui.stat-card label="Total Baris" :value="number_format($summary['trx'], 0, ',', '.')" icon="bi-list-ol" variant="primary" />
        <x-ui.stat-card label="Total Masuk" :value="number_format($summary['in'], 0, ',', '.')" icon="bi-arrow-down-circle" variant="success" />
        <x-ui.stat-card label="Total Keluar" :value="number_format($summary['out'], 0, ',', '.')" icon="bi-arrow-up-circle" variant="danger" />
    </div>

    <x-ui.card>
        <form method="GET" class="filters">
            <div class="filters__search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari nomor referensi…">
            </div>

            <select name="type" class="form-select">
                <option value="">Semua tipe</option>
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected($filters['type'] === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>

            <select name="product_id" class="form-select">
                <option value="">Semua produk</option>
                @foreach ($products as $id => $name)
                    <option value="{{ $id }}" @selected((string) $filters['product_id'] === (string) $id)>{{ $name }}</option>
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
            <a href="{{ route('laporan.index') }}" class="btn btn--ghost">Reset</a>
        </form>

        @if ($moves->isEmpty())
            <x-ui.empty title="Tidak ada data" description="Ubah penyaring atau rentang tanggal untuk melihat mutasi lain." />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Nomor</th>
                        <th>Tipe</th>
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
                            <td><x-ui.badge :variant="$move->type->badge()">{{ $move->typeLabel() }}</x-ui.badge></td>
                            <td>{{ $move->product->name }}</td>
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
