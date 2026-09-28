<x-layouts.app title="Cetak Laporan" active="laporan">
    <header class="page-header page-header--print">
        <div>
            <h1 class="page-header__title">Pratinjau Cetak</h1>
            <p class="page-header__subtitle">Gunakan dialog cetak browser, pilih A4 dan aktifkan “Save as PDF”.</p>
        </div>

        <div class="page-header__actions page-header__actions--print">
            <button type="button" class="btn btn--primary" data-print><i class="bi bi-printer"></i> Cetak</button>
            <a href="{{ route('laporan.index', $filters) }}" class="btn btn--ghost">Kembali</a>
        </div>
    </header>

    <x-ui.card class="print-sheet">
        <div class="print-sheet__head">
            <h2>{{ config('app.name') }}</h2>
            <p>Laporan Mutasi Stok</p>
            <small>
                Periode:
                {{ $filters['from'] ? \Illuminate\Support\Carbon::parse($filters['from'])->translatedFormat('d M Y') : 'awal' }}
                s.d.
                {{ $filters['to'] ? \Illuminate\Support\Carbon::parse($filters['to'])->translatedFormat('d M Y') : 'hari ini' }}
                @if ($filters['type'])
                    · {{ \App\Enums\StockMoveType::tryFrom($filters['type'])?->label() ?? $filters['type'] }}
                @endif
            </small>
        </div>

        <div class="table-wrap">
            <table class="table table--print">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu</th>
                    <th>Nomor</th>
                    <th>Tipe</th>
                    <th>Produk</th>
                    <th>Lokasi</th>
                    <th class="text--right">Qty</th>
                    <th class="text--right">Stok Akhir</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($moves as $index => $move)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $move->created_at->translatedFormat('d/m/Y H:i') }}</td>
                        <td class="mono">{{ $move->reference }}</td>
                        <td>{{ $move->typeLabel() }}</td>
                        <td>{{ $move->product->name }}</td>
                        <td>{{ $move->fromLocation?->name ?? '—' }} &rarr; {{ $move->toLocation?->name ?? '—' }}</td>
                        <td class="text--right mono">{{ $move->qty }}</td>
                        <td class="text--right mono">{{ $move->stock_after }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="print-sheet__foot">
            <span>Total baris: {{ $summary['trx'] }} · Masuk: {{ $summary['in'] }} · Keluar: {{ $summary['out'] }}</span>
            <span>Dicetak oleh {{ auth()->user()->name }} pada {{ now()->translatedFormat('d M Y H:i') }}</span>
        </div>
    </x-ui.card>
</x-layouts.app>
