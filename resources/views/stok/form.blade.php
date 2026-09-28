<x-layouts.app :title="$pageTitle" :active="$routePrefix">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">{{ $pageTitle }}</h1>
            <p class="page-header__subtitle">Formulir pencatatan {{ strtolower($pageTitle) }}.</p>
        </div>
    </header>

    <x-ui.card>
        <form method="POST" action="{{ route($routePrefix.'.store') }}" class="stack">
            @csrf

            <x-ui.form.select name="product_id" label="Produk" :options="$products" required />

            @if ($variant === 'transfer')
                <div class="form-row">
                    <x-ui.form.select name="from_location_id" label="Lokasi Asal" :options="$locations" required />
                    <x-ui.form.select name="to_location_id" label="Lokasi Tujuan" :options="$locations" required />
                </div>
                <x-ui.form.input name="qty" label="Jumlah Dipindahkan" type="number" min="1" :step="1" required />
            @elseif ($variant === 'return')
                <x-ui.form.select
                    name="direction"
                    label="Jenis Retur"
                    :options="['retur_masuk' => 'Retur Masuk (dari pelanggan)', 'retur_keluar' => 'Retur Keluar (ke supplier)']"
                    placeholder="— Pilih jenis retur —"
                    required
                />
                <x-ui.form.select name="location_id" label="Lokasi" :options="$locations" required />
                <div class="form-row">
                    <x-ui.form.input name="qty" label="Jumlah" type="number" min="1" :step="1" required />
                    <x-ui.form.input name="reference" label="Nomor Referensi" placeholder="Opsional" />
                </div>
            @elseif ($variant === 'adjustment')
                <x-ui.form.select name="location_id" label="Lokasi" :options="$locations" required />
                <x-ui.form.input
                    name="physical_qty"
                    label="Hasil Opname Fisik"
                    type="number"
                    min="0"
                    :step="1"
                    required
                    hint="Selisih terhadap stok sistem akan dicatat otomatis sebagai penyesuaian."
                />
            @else
                <x-ui.form.select name="location_id" label="Lokasi" :options="$locations" required />
                <div class="form-row">
                    <x-ui.form.input name="qty" label="Jumlah" type="number" min="1" :step="1" required />
                    <x-ui.form.input name="reference" label="Nomor Referensi" placeholder="Opsional" />
                </div>
            @endif

            <x-ui.stock-availability :matrix="$stockMatrix" :locations="$locations" />

            <x-ui.form.textarea name="note" label="Catatan" rows="2" />

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">Simpan</button>
                <a href="{{ route($routePrefix.'.index') }}" class="btn btn--ghost">Batal</a>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
