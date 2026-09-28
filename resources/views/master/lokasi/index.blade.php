<x-layouts.app title="Lokasi Penyimpanan" active="lokasi">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">Lokasi Penyimpanan</h1>
            <p class="page-header__subtitle">Toko atau gudang sebagai tujuan dan sumber stok.</p>
        </div>

        <div class="page-header__actions">
            <button type="button" class="btn btn--primary" data-modal-open="modal-lokasi">
                <i class="bi bi-plus-lg"></i> Tambah Lokasi
            </button>
        </div>
    </header>

    <x-ui.card title="Daftar Lokasi" icon="bi-geo-alt">
        <form method="GET" class="filters filters--compact">
            <div class="filters__search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari lokasi…">
            </div>
            <button type="submit" class="btn btn--primary">Cari</button>
        </form>

        @if ($locations->isEmpty())
            <x-ui.empty title="Belum ada lokasi" />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr><th>Kode</th><th>Nama</th><th>Keterangan</th><th class="text--right">Aksi</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($locations as $location)
                        <tr>
                            <form id="lokasi-{{ $location->id }}" method="POST" action="{{ route('lokasi.update', $location) }}">
                                @csrf
                                @method('PUT')
                                <td><input class="form-input form-input--inline mono" name="code" value="{{ $location->code }}" required></td>
                                <td><input class="form-input form-input--inline" name="name" value="{{ $location->name }}" required></td>
                                <td><input class="form-input form-input--inline" name="description" value="{{ $location->description }}"></td>
                            </form>
                            <td class="text--right">
                                <div class="row-actions row-actions--end">
                                    <button type="submit" form="lokasi-{{ $location->id }}" class="btn btn--ghost btn--sm" aria-label="Simpan {{ $location->name }}"><i class="bi bi-check-lg"></i></button>
                                    <x-ui.delete-form :action="route('lokasi.destroy', $location)" :message="'Hapus lokasi '.$location->name.'?'" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{ $locations->links('pagination::inventori') }}
        @endif
    </x-ui.card>

    <x-ui.modal id="modal-lokasi" title="Tambah Lokasi" icon="bi-plus-circle" :action="route('lokasi.store')">
        <x-ui.form.input name="code" label="Kode" required placeholder="TKO-01" />
        <x-ui.form.input name="name" label="Nama Lokasi" required placeholder="Toko Pusat" />
        <x-ui.form.textarea name="description" label="Keterangan" rows="2" />
        <x-ui.form.checkbox name="is_active" label="Lokasi aktif" :checked="true" />
    </x-ui.modal>
</x-layouts.app>
