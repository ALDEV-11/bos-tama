@props(['id', 'title', 'action', 'icon' => null, 'submit' => 'Simpan'])

<dialog class="modal modal--form" id="{{ $id }}" aria-labelledby="{{ $id }}-title">
    <form method="POST" action="{{ $action }}" class="modal__form">
        @csrf

        <div class="modal__head">
            <h2 class="modal__title" id="{{ $id }}-title">
                @if ($icon)<i class="bi {{ $icon }}" aria-hidden="true"></i>@endif
                {{ $title }}
            </h2>

            <button type="button" class="modal__close" data-modal-close aria-label="Tutup dialog">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>

        <div class="modal__body stack">
            {{ $slot }}
        </div>

        <div class="modal__actions">
            <button type="submit" class="btn btn--primary">{{ $submit }}</button>
            <button type="button" class="btn btn--ghost" data-modal-close>Batal</button>
        </div>
    </form>
</dialog>
