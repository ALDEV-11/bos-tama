@props(['variant' => 'neutral', 'icon' => null])

<span {{ $attributes->merge(['class' => 'badge badge--'.$variant]) }}>
    @if ($icon)<i class="bi {{ $icon }}"></i>@endif
    {{ $slot }}
</span>
