@props(['label', 'value', 'icon', 'variant' => 'primary', 'hint' => null])

<div {{ $attributes->merge(['class' => 'stat-card stat-card--'.$variant]) }}>
    <div class="stat-card__icon"><i class="bi {{ $icon }}"></i></div>
    <div class="stat-card__body">
        <div class="stat-card__value mono">{{ $value }}</div>
        <div class="stat-card__label">{{ $label }}</div>
        @if ($hint)<div class="stat-card__label">{{ $hint }}</div>@endif
    </div>
</div>
