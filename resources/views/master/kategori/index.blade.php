<x-layouts.app title="Kategori" active="kategori">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">Kategori</h1>
            <p class="page-header__subtitle">Kelompok produk untuk memudahkan pencarian.</p>
        </div>

        <div class="page-header__actions">
            <button type="button" class="btn btn--primary" data-modal-open="modal-kategori">
                <i class="bi bi-plus-lg"></i> Tambah Kategori
            </button>
        </div>
    </header>

    <x-ui.card title="Daftar Kategori" icon="bi-tags">
        <form method="GET" class="filters filters--compact">
            <div class="filters__search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari kategori…">
            </div>
            <button type="submit" class="btn btn--primary">Cari</button>
        </form>

        @if ($categories->isEmpty())
            <x-ui.empty title="Belum ada kategori" />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr><th>Nama</th><th>Keterangan</th><th class="text--right">Aksi</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <form id="kategori-{{ $category->id }}" method="POST" action="{{ route('kategori.update', $category) }}">
                                @csrf
                                @method('PUT')
                                <td><input class="form-input form-input--inline" name="name" value="{{ $category->name }}" required></td>
                                <td><input class="form-input form-input--inline" name="description" value="{{ $category->description }}"></td>
                            </form>
                            <td class="text--right">
                                <div class="row-actions row-actions--end">
                                    <button type="submit" form="kategori-{{ $category->id }}" class="btn btn--ghost btn--sm" aria-label="Simpan {{ $category->name }}"><i class="bi bi-check-lg"></i></button>
                                    <x-ui.delete-form :action="route('kategori.destroy', $category)" :message="'Hapus kategori '.$category->name.'?'" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{ $categories->links('pagination::inventori') }}
        @endif
    </x-ui.card>

    <x-ui.modal id="modal-kategori" title="Tambah Kategori" icon="bi-plus-circle" :action="route('kategori.store')">
        <x-ui.form.input name="name" label="Nama Kategori" required placeholder="Contoh: alat" />
        <x-ui.form.textarea name="description" label="Keterangan" rows="2" />
    </x-ui.modal>
</x-layouts.app>
