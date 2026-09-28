@php
    $flashItems = [];

    if (session('success')) {
        $flashItems[] = ['type' => 'success', 'title' => session('success'), 'details' => []];
    }

    if (session('error')) {
        $flashItems[] = ['type' => 'error', 'title' => session('error'), 'details' => []];
    }

    if ($errors->any()) {
        $flashItems[] = [
            'type' => 'error',
            'title' => 'Periksa kembali isian Anda.',
            'details' => $errors->all(),
        ];
    }
@endphp

@if ($flashItems !== [])
    <script type="application/json" data-flash>@json($flashItems)</script>
@endif
