<x-layouts.app title="Laporan Persediaan" active="persediaan">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">Laporan Persediaan</h1>
            <p class="page-header__subtitle">Posisi stok terkini beserta nilai persediaan.</p>
        </div>

        <div class="page-header__actions">
            <a href="{{ route('persediaan.unduh', $filters) }}" class="btn btn--primary"><i class="bi bi-download"></i> Unduh CSV</a>
        </div>
    </header>

    <x-ui.card>
        <form method="GET" class="filters">
            <div class="filters__search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari SKU atau nama produk…">
            </div>

            <select name="category_id" class="form-select">
                <option value="">Semua kategori</option>
                @foreach ($categories as $id => $name)
                    <option value="{{ $id }}" @selected((string) $filters['category_id'] === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>

            <select name="status" class="form-select">
                <option value="">Semua status stok</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn--primary">Terapkan</button>
            <a href="{{ route('persediaan.index') }}" class="btn btn--ghost">Reset</a>
        </form>

        @if ($products->isEmpty())
            <x-ui.empty title="Tidak ada data" description="Belum ada produk yang cocok dengan penyaring." />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Supplier</th>
                        <th class="text--right">Stok</th>
                        <th class="text--right">Min</th>
                        <th class="text--right">Harga</th>
                        <th class="text--right">Nilai</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td class="mono">{{ $product->sku }}</td>
                            <td><a href="{{ route('produk.show', $product) }}"><strong>{{ $product->name }}</strong></a></td>
                            <td>{{ $product->category->name }}</td>
                            <td>{{ $product->supplier?->name ?? '-' }}</td>
                            <td class="text--right mono">{{ $product->stock }} {{ $product->unit }}</td>
                            <td class="text--right mono text--muted">{{ $product->min_stock }}</td>
                            <td class="text--right mono">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td class="text--right mono">Rp {{ number_format($product->stockValue(), 0, ',', '.') }}</td>
                            <td><x-ui.stock-badge :product="$product" /></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{ $products->links('pagination::inventori') }}
        @endif
    </x-ui.card>
</x-layouts.app>
