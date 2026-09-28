<x-layouts.guest title="Daftar">
    <x-ui.card>
        <h1 class="auth__title">Buat Akun Baru</h1>
        <p class="text--muted">Akun baru otomatis mendapat role petugas.</p>

        <form method="POST" action="{{ route('register.store') }}" class="stack">
            @csrf

            <x-ui.form.input name="name" label="Nama Lengkap" required autofocus placeholder="Nama lengkap" />

            <x-ui.form.input name="email" label="Email" type="email" required placeholder="nama@email.com" />

            <x-ui.form.input name="phone" label="No. HP" placeholder="08xxxxxxxxxx" />

            <div class="form-row">
                <x-ui.form.input name="password" label="Password" type="password" required placeholder="Minimal 8 karakter" />
                <x-ui.form.input name="password_confirmation" label="Ulangi Password" type="password" required />
            </div>

            <x-ui.button type="submit" class="w-full">Daftar</x-ui.button>
        </form>

        <p class="auth__foot">
            Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
        </p>
    </x-ui.card>
</x-layouts.guest>
