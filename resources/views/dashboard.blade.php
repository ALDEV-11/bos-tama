<x-layouts.app title="Dashboard" active="dashboard" body-class="app--viewport">
    <div class="dashboard">
        <header class="page-header page-header--tight">
            <div>
                <h1 class="page-header__title">Dashboard</h1>
                <p class="page-header__subtitle">Ringkasan stok dan aktivitas terbaru.</p>
            </div>
            <div class="page-header__actions">
                <a href="{{ route('stok-masuk.create') }}" class="btn btn--primary"><i class="bi bi-plus-lg"></i> Stok Masuk</a>
                <a href="{{ route('stok-keluar.create') }}" class="btn btn--outline"><i class="bi bi-plus-lg"></i> Stok Keluar</a>
            </div>
        </header>

        <section class="stat-grid dashboard__stats">
            <x-ui.stat-card label="Produk Aktif" :value="$summary['total_products']" icon="bi-box-seam" variant="primary" />
            <x-ui.stat-card label="Total Unit Stok" :value="number_format($summary['total_stock'], 0, ',', '.')" icon="bi-boxes" variant="info" />
            <x-ui.stat-card label="Nilai Persediaan" :value="'Rp '.number_format($summary['stock_value'], 0, ',', '.')" icon="bi-cash-coin" variant="success" />
            <x-ui.stat-card label="Stok Menipis" :value="$summary['low_stock']" icon="bi-exclamation-triangle" variant="warning" />
            <x-ui.stat-card label="Stok Habis" :value="$summary['out_stock']" icon="bi-x-octagon" variant="danger" />
        </section>

        <div class="grid-2 dashboard__panels">
            <x-ui.card title="Mutasi Stok 7 Hari Terakhir" class="card--fill">
                <div class="chart">
                    @php($peak = max(1, collect($chart)->max(fn ($row) => max($row['in'], $row['out']))))
                    @foreach ($chart as $row)
                        <div class="chart__day" title="{{ $row['label'] }}: masuk {{ $row['in'] }}, keluar {{ $row['out'] }}">
                            <div class="chart__bars">
                                <span class="chart__bar chart__bar--in" style="height: {{ round($row['in'] / $peak * 100) }}%"></span>
                                <span class="chart__bar chart__bar--out" style="height: {{ round($row['out'] / $peak * 100) }}%"></span>
                            </div>
                            <small>{{ $row['label'] }}</small>
                        </div>
                    @endforeach
                </div>

                <div class="chart-legend">
                    <span><i class="dot dot--success"></i> Stok masuk</span>
                    <span><i class="dot dot--danger"></i> Stok keluar</span>
                </div>
            </x-ui.card>

            <x-ui.card title="Perlu Perhatian" icon="bi-exclamation-triangle" class="card--fill">
                <div class="dashboard__attention">
                    @forelse ($lowStock as $product)
                        <a href="{{ route('produk.show', $product) }}" class="attention">
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <small class="text--muted">{{ $product->sku }} · min {{ $product->min_stock }} {{ $product->unit }}</small>
                            </div>
                            <x-ui.stock-badge :product="$product" />
                        </a>
                    @empty
                        <x-ui.empty title="Semua stok aman" description="Tidak ada produk yang melewati batas minimum." />
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <x-ui.card title="Mutasi Terbaru" icon="bi-clock-history">
            @if ($recentMoves->isNotEmpty())
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Nomor</th>
                            <th>Tipe</th>
                            <th>Produk</th>
                            <th class="text--right">Jumlah</th>
                            <th class="text--right">Stok</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($recentMoves as $move)
                            <tr>
                                <td class="text--muted">@timeago($move->created_at)</td>
                                <td class="mono">{{ $move->reference }}</td>
                                <td><x-ui.badge :variant="$move->type->badge()">{{ $move->typeLabel() }}</x-ui.badge></td>
                                <td><a href="{{ route('produk.show', $move->product_id) }}">{{ $move->product->name }}</a></td>
                                <td class="text--right mono">{{ $move->qty }}</td>
                                <td class="text--right mono">{{ $move->stock_after }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-ui.empty title="Belum ada mutasi stok" description="Mutasi stok yang kamu catat akan tampil di sini." />
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
