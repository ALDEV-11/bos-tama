@props(['variant' => 'info', 'dismissible' => true, 'icon' => 'bi-info-circle'])

<div {{ $attributes->merge(['class' => 'alert alert--'.$variant]) }} role="alert">
    <i class="bi {{ $icon }}"></i>
    <div>{{ $slot }}</div>
    @if ($dismissible)
        <button type="button" class="alert__close" data-alert-close aria-label="Tutup pesan">
            <i class="bi bi-x-lg"></i>
        </button>
    @endif
</div>
