@props(['title' => null])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{ $title ? $title.' · '.config('app.name') : config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth">
<div class="auth__wrapper">
    <div class="auth__aside">
        <span class="sidebar__brand-mark"><i class="bi bi-box-seam"></i></span>
        <h1>{{ config('app.name') }}</h1>
        <p>Kelola stok, mutasi, dan notifikasi Vascon dalam satu tempat.</p>
        <ul class="auth__points">
            <li><i class="bi bi-bell"></i> Notifikasi otomatis saat stok menipis</li>
            <li><i class="bi bi-arrow-left-right"></i> Transfer antar lokasi</li>
            <li><i class="bi bi-clipboard-data"></i> Laporan siap cetak dan ekspor</li>
        </ul>
    </div>

    <div class="auth__panel">
        <div class="auth__form">
            @include('partials.flash')
            {{ $slot }}
        </div>
    </div>
</div>
</body>
</html>
