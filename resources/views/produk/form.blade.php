<x-layouts.app :title="$product ? 'Ubah Produk' : 'Tambah Produk'" active="produk">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">{{ $product ? 'Ubah Produk' : 'Tambah Produk' }}</h1>
            <p class="page-header__subtitle">Informasi produk, harga jual, dan batas stok minimum.</p>
        </div>
    </header>

    <x-ui.card>
        <form method="POST" action="{{ $product ? route('produk.update', $product) : route('produk.store') }}" class="stack">
            @csrf
            @if ($product) @method('PUT') @endif

            <div class="form-row">
                <x-ui.form.input name="sku" label="SKU" :value="$product?->sku" required placeholder="VSC-001" />
                <x-ui.form.input name="name" label="Nama Produk" :value="$product?->name" required placeholder="Nama produk" />
            </div>

            <div class="form-row">
                <x-ui.form.select name="category_id" label="Kategori" :options="$categories" :selected="$product?->category_id" required />
                <x-ui.form.select name="supplier_id" label="Supplier" :options="$suppliers" :selected="$product?->supplier_id" placeholder="Tanpa supplier" />
            </div>

            <div class="form-row form-row--three">
                <x-ui.form.input name="unit" label="Satuan" :value="$product?->unit ?? 'pcs'" required />
                <x-ui.form.input name="price" label="Harga (Rp)" type="number" :value="$product?->price" :step="1" required min="0" />
                <x-ui.form.input name="min_stock" label="Stok Minimum" type="number" :value="$product?->min_stock ?? 5" :step="1" required min="0" />
            </div>

            <x-ui.form.textarea name="description" label="Deskripsi" :value="$product?->description" />

            <x-ui.form.checkbox name="is_active" label="Produk aktif" :checked="$product?->is_active ?? true" />

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Simpan</button>
                <a href="{{ route('produk.index') }}" class="btn btn--ghost">Batal</a>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
