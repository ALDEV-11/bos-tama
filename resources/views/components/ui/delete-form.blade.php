@props(['action', 'label' => 'Hapus', 'message' => 'Data ini akan dihapus permanen. Lanjutkan?', 'icon' => 'bi-trash'])

<form method="POST" action="{{ $action }}" data-confirm-form data-confirm="{{ $message }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn--outline btn--sm">
        <i class="bi {{ $icon }}"></i> {{ $label }}
    </button>
</form>
