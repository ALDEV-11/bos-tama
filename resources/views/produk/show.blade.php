<x-layouts.app :title="$product->name" active="produk">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">{{ $product->name }}</h1>
            <p class="page-header__subtitle mono">{{ $product->sku }} · {{ $product->category->name }}</p>
        </div>

        <div class="page-header__actions">
            @if (auth()->user()->isAdmin())
                <a href="{{ route('produk.edit', $product) }}" class="btn btn--outline"><i class="bi bi-pencil"></i> Ubah</a>
            @endif
            <a href="{{ route('produk.index') }}" class="btn btn--ghost">Kembali</a>
        </div>
    </header>

    <div class="grid-2">
        <x-ui.card title="Informasi Produk" icon="bi-box-seam">
            <dl class="detail-list">
                <div><dt>Kategori</dt><dd>{{ $product->category->name }}</dd></div>
                <div><dt>Supplier</dt><dd>{{ $product->supplier?->name ?? '-' }}</dd></div>
                <div><dt>Satuan</dt><dd>{{ $product->unit }}</dd></div>
                <div><dt>Harga</dt><dd>Rp {{ number_format($product->price, 0, ',', '.') }}</dd></div>
                <div><dt>Stok minimum</dt><dd>{{ $product->min_stock }} {{ $product->unit }}</dd></div>
                <div><dt>Status stok</dt><dd><x-ui.stock-badge :product="$product" /></dd></div>
                <div><dt>Status produk</dt><dd>{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
                @if ($product->description)
                    <div><dt>Deskripsi</dt><dd>{{ $product->description }}</dd></div>
                @endif
            </dl>
        </x-ui.card>

        <x-ui.card title="Stok per Lokasi" icon="bi-geo-alt">
            @if ($product->locationStocks->isEmpty())
                <x-ui.empty title="Belum ada stok" description="Catat stok masuk untuk menambah jumlah stok produk ini." />
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr><th>Lokasi</th><th class="text--right">Qty</th></tr>
                        </thead>
                        <tbody>
                        @foreach ($product->locationStocks as $stock)
                            <tr>
                                <td>{{ $stock->location->name }}</td>
                                <td class="text--right mono">{{ $stock->qty }} {{ $product->unit }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>Total</th>
                            <th class="text--right mono">{{ $product->stock }} {{ $product->unit }}</th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>

    <x-ui.card title="Riwayat Mutasi" icon="bi-clock-history">
        @if ($moves->isEmpty())
            <x-ui.empty title="Belum ada mutasi" description="Riwayat stok produk ini akan tampil di sini." />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Nomor</th>
                        <th>Tipe</th>
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
