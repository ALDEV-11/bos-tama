@props(['title' => null, 'active' => null, 'bodyClass' => null])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ $title ? $title.' · '.config('app.name') : config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app{{ $bodyClass ? ' '.$bodyClass : '' }}">
<button type="button" class="sidebar__backdrop" data-sidebar-toggle aria-label="Tutup menu"></button>

<aside class="sidebar" data-sidebar>
    <div class="sidebar__brand">
        <span class="sidebar__brand-mark"><i class="bi bi-box-seam"></i></span>
        <span class="sidebar__brand-text">
            <strong>{{ config('app.name') }}</strong>
            <small>Sistem Inventori</small>
        </span>
    </div>

    @include('partials.sidebar', ['active' => $active])
</aside>

<div class="app__main">
    <header class="topbar">
        <button type="button" class="topbar__burger" data-sidebar-toggle aria-label="Buka menu">
            <i class="bi bi-list"></i>
        </button>

        <div class="topbar__actions">
            @include('partials.notifications-bell', [
                'unreadCount' => $unreadCount ?? 0,
                'latestNotifications' => $latestNotifications ?? collect(),
            ])

            <div class="user-menu" data-dropdown>
                <button type="button" class="user-menu__toggle" data-dropdown-toggle aria-expanded="false">
                    <span class="avatar">{{ Str::of(auth()->user()->name)->substr(0, 1)->upper() }}</span>
                    <span class="user-menu__meta">
                        <strong>{{ auth()->user()->name }}</strong>
                        <small>{{ auth()->user()->roleLabel() }}</small>
                    </span>
                    <i class="bi bi-chevron-down"></i>
                </button>

                <div class="user-menu__panel" data-dropdown-panel>
                    <div class="user-menu__header">
                        <strong>{{ auth()->user()->name }}</strong>
                        <small>{{ auth()->user()->email }}</small>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="user-menu__item">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="content">
        @include('partials.flash-swal')
        {{ $slot }}
    </main>

    <footer class="footer">
        <span>&copy; {{ date('Y') }} {{ config('app.name') }}</span>
        <span>Sistem Inventori &amp; Notifikasi Stok</span>
    </footer>
</div>

<x-ui.confirm-modal />

@stack('scripts')
</body>
</html>
