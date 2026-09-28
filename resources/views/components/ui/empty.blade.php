@props(['icon' => 'bi-inbox', 'title' => 'Belum ada data', 'description' => null])

<div class="empty">
    <i class="bi {{ $icon }} empty__icon"></i>
    <div class="empty__title">{{ $title }}</div>
    @if ($description)<p>{{ $description }}</p>@endif
    {{ $slot }}
</div>
