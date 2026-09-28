@if (session('success') || session('error') || $errors->any())
    <div class="toast" data-toast>
        @if (session('success'))
            <x-ui.alert variant="success" icon="bi-check-circle">{{ session('success') }}</x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert variant="danger" icon="bi-x-octagon">{{ session('error') }}</x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert variant="danger" icon="bi-exclamation-triangle">
                <strong>Periksa kembali isian Anda.</strong>
                <ul class="alert__list">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif
    </div>
@endif
