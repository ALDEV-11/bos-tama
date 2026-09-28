@props(['variant' => 'primary', 'size' => null, 'icon' => null, 'type' => 'button'])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'btn btn--'.$variant.($size ? ' btn--'.$size : '')]) }}
>
    @if ($icon)<i class="bi {{ $icon }}"></i>@endif
    {{ $slot }}
</button>
