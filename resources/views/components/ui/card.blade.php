@props(['title' => null, 'subtitle' => null, 'icon' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($actions))
        <div class="card__header">
            <div class="card__heading">
                @if ($icon)<i class="bi {{ $icon }} card__icon"></i>@endif
                <div>
                    @if ($title)<h2 class="card__title">{{ $title }}</h2>@endif
                    @if ($subtitle)<p class="card__subtitle">{{ $subtitle }}</p>@endif
                </div>
            </div>
            @isset($actions)
                <div class="cluster">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{ $slot }}

    @if ($footer)
        <div class="card__footer">{{ $footer }}</div>
    @endif
</div>
