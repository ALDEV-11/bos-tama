@props(['route', 'icon', 'active' => false, 'badge' => null])

<a href="{{ route($route) }}" class="sidebar__item {{ $active ? 'sidebar__item--active' : '' }}">
    <i class="bi {{ $icon }} sidebar__item-icon"></i>
    <span>{{ $slot }}</span>
    @if ($badge)
        <span class="sidebar__item-badge">{{ $badge }}</span>
    @endif
</a>
