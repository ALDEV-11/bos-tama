<x-layouts.app title="Pengguna" active="pengguna">
    <header class="page-header">
        <div>
            <h1 class="page-header__title">Pengguna</h1>
            <p class="page-header__subtitle">Akun yang dapat mengakses sistem beserta perannya.</p>
        </div>

        <div class="page-header__actions">
            <button type="button" class="btn btn--primary" data-modal-open="modal-pengguna">
                <i class="bi bi-plus-lg"></i> Tambah Pengguna
            </button>
        </div>
    </header>

    <x-ui.card title="Daftar Pengguna" icon="bi-people">
        <form method="GET" class="filters filters--compact">
            <div class="filters__search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau email…">
            </div>
            <button type="submit" class="btn btn--primary">Cari</button>
        </form>

        @if ($users->isEmpty())
            <x-ui.empty title="Belum ada pengguna lain" />
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text--right">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <form id="pengguna-{{ $user->id }}" method="POST" action="{{ route('pengguna.update', $user) }}">
                                @csrf
                                @method('PUT')
                                <td><input class="form-input form-input--inline" name="name" value="{{ $user->name }}" required></td>
                                <td><input class="form-input form-input--inline" name="email" type="email" value="{{ $user->email }}" required></td>
                                <td>
                                    <select class="form-select form-select--inline" name="role_id">
                                        @foreach ($roles as $id => $label)
                                            <option value="{{ $id }}" @selected($user->role_id === $id)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" name="is_active" value="0">
                                    <label class="form-check form-check--compact">
                                        <input type="checkbox" name="is_active" value="1" @checked($user->is_active)>
                                        <span>Aktif</span>
                                    </label>
                                </td>
                            </form>
                            <td class="text--right">
                                <div class="row-actions row-actions--end">
                                    <button type="submit" form="pengguna-{{ $user->id }}" class="btn btn--ghost btn--sm" aria-label="Simpan {{ $user->name }}"><i class="bi bi-check-lg"></i></button>
                                    <x-ui.delete-form :action="route('pengguna.destroy', $user)" :message="'Hapus akun '.$user->name.'?'" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{ $users->links('pagination::inventori') }}
        @endif
    </x-ui.card>

    <x-ui.modal id="modal-pengguna" title="Tambah Pengguna" icon="bi-person-plus" :action="route('pengguna.store')">
        <x-ui.form.input name="name" label="Nama Lengkap" required />
        <x-ui.form.input name="email" label="Email" type="email" required />
        <x-ui.form.input name="phone" label="No. HP" />
        <x-ui.form.select name="role_id" label="Role" :options="$roles" required />

        <div class="form-row">
            <x-ui.form.input name="password" label="Password" type="password" required />
            <x-ui.form.input name="password_confirmation" label="Ulangi Password" type="password" required />
        </div>

        <x-ui.form.checkbox name="is_active" label="Akun aktif" :checked="true" />
    </x-ui.modal>
</x-layouts.app>
