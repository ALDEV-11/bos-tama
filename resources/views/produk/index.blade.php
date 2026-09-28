<x-layouts.app title="Produk" active="produk">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">Produk</h1>
            <p class="page-header__subtitle">Katalog produk, harga, dan batas minimum stok.</p>
        </div>

        @if (auth()->user()->isAdmin())
            <div class="page-header__actions">
                <a href="{{ route('produk.create') }}" class="btn btn--primary"><i class="bi bi-plus-lg"></i> Tambah Produk</a>
            </div>
        @endif
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

            <select name="is_active" class="form-select">
                <option value="">Semua status produk</option>
                <option value="1" @selected($filters['is_active'] === '1')>Aktif</option>
                <option value="0" @selected($filters['is_active'] === '0')>Nonaktif</option>
            </select>

            <button type="submit" class="btn btn--primary">Terapkan</button>
            <a href="{{ route('produk.index') }}" class="btn btn--ghost">Reset</a>
        </form>

        @if ($products->isEmpty())
            <x-ui.empty title="Belum ada produk" description="Tambahkan produk pertama untuk mulai mencatat stok." />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th><a href="{{ route('produk.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'sku', 'direction' => 'asc'])) }}">SKU</a></th>
                        <th><a href="{{ route('produk.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'name', 'direction' => 'asc'])) }}">Produk</a></th>
                        <th>Kategori</th>
                        <th class="text--right">Stok</th>
                        <th class="text--right">Min</th>
                        <th class="text--right">Harga</th>
                        <th>Status</th>
                        @if (auth()->user()->isAdmin())<th class="text--right">Aksi</th>@endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td class="mono">{{ $product->sku }}</td>
                            <td>
                                <a href="{{ route('produk.show', $product) }}"><strong>{{ $product->name }}</strong></a>
                                @unless ($product->is_active)<x-ui.badge variant="muted">Nonaktif</x-ui.badge>@endunless
                            </td>
                            <td>{{ $product->category->name }}</td>
                            <td class="text--right mono">{{ $product->stock }} {{ $product->unit }}</td>
                            <td class="text--right mono text--muted">{{ $product->min_stock }}</td>
                            <td class="text--right mono">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td><x-ui.stock-badge :product="$product" /></td>
                            @if (auth()->user()->isAdmin())
                                <td class="text--right">
                                    <div class="row-actions">
                                        <a href="{{ route('produk.edit', $product) }}" class="btn btn--ghost btn--sm" aria-label="Ubah {{ $product->name }}"><i class="bi bi-pencil"></i></a>
                                        <x-ui.delete-form :action="route('produk.destroy', $product)" :message="'Hapus produk '.$product->name.'?'" icon="bi-trash" label="" />
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{ $products->links('pagination::inventori') }}
        @endif
    </x-ui.card>
</x-layouts.app>
