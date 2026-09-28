<x-layouts.app title="Supplier" active="supplier">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">Supplier</h1>
            <p class="page-header__subtitle">Mitra pemasok barang.</p>
        </div>

        <div class="page-header__actions">
            <button type="button" class="btn btn--primary" data-modal-open="modal-supplier">
                <i class="bi bi-plus-lg"></i> Tambah Supplier
            </button>
        </div>
    </header>

    <x-ui.card title="Daftar Supplier" icon="bi-truck">
        <form method="GET" class="filters filters--compact">
            <div class="filters__search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari supplier…">
            </div>
            <button type="submit" class="btn btn--primary">Cari</button>
        </form>

        @if ($suppliers->isEmpty())
            <x-ui.empty title="Belum ada supplier" />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kontak</th>
                        <th>No. HP</th>
                        <th class="text--right">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr>
                            <form id="supplier-{{ $supplier->id }}" method="POST" action="{{ route('supplier.update', $supplier) }}">
                                @csrf
                                @method('PUT')
                                <td>
                                    <input class="form-input form-input--inline" name="name" value="{{ $supplier->name }}" required>
                                    <input class="form-input form-input--inline" name="contact_name" value="{{ $supplier->contact_name }}" placeholder="kontak">
                                </td>
                                <td><input class="form-input form-input--inline" name="email" type="email" value="{{ $supplier->email }}" placeholder="email"></td>
                                <td><input class="form-input form-input--inline" name="phone" value="{{ $supplier->phone }}" placeholder="08…"></td>
                            </form>
                            <td class="text--right">
                                <div class="row-actions row-actions--end">
                                    <button type="submit" form="supplier-{{ $supplier->id }}" class="btn btn--ghost btn--sm" aria-label="Simpan {{ $supplier->name }}"><i class="bi bi-check-lg"></i></button>
                                    <x-ui.delete-form :action="route('supplier.destroy', $supplier)" :message="'Hapus supplier '.$supplier->name.'?'" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{ $suppliers->links('pagination::inventori') }}
        @endif
    </x-ui.card>

    <x-ui.modal id="modal-supplier" title="Tambah Supplier" icon="bi-plus-circle" :action="route('supplier.store')">
        <x-ui.form.input name="name" label="Nama Supplier" required />
        <x-ui.form.input name="contact_name" label="Nama Kontak" />
        <x-ui.form.input name="phone" label="No. HP" />
        <x-ui.form.input name="email" label="Email" type="email" />
        <x-ui.form.textarea name="address" label="Alamat" rows="2" />
        <x-ui.form.checkbox name="is_active" label="Supplier aktif" :checked="true" />
    </x-ui.modal>
</x-layouts.app>
