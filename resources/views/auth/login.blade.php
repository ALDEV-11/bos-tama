<x-layouts.guest title="Masuk">
    <x-ui.card>
        <h1 class="auth__title">Masuk ke Akun</h1>
        <p class="text--muted">Gunakan email dan password yang terdaftar.</p>

        <form method="POST" action="{{ route('login.store') }}" class="stack">
            @csrf

            <x-ui.form.input name="email" label="Email" type="email" required autofocus placeholder="nama@email.com" />

            <x-ui.form.input name="password" label="Password" type="password" required placeholder="••••••••" />

            <label class="form-check">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>Ingat saya di perangkat ini</span>
            </label>

            <x-ui.button type="submit" class="w-full">Masuk</x-ui.button>
        </form>

        <p class="auth__foot">
            Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
        </p>
    </x-ui.card>
</x-layouts.guest>
