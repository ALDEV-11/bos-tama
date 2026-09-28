<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Enums\StockMoveType;
use App\Enums\StockStatus;
use App\Support\PageSize;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly StockMoveRepositoryInterface $moves,
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories,
        private readonly LocationRepositoryInterface $locations,
    ) {}

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('laporan.mutasi', [
            'moves' => $this->moves->paginate($filters, PageSize::TRANSACTIONS),
            'summary' => $this->moves->summary($filters),
            'filters' => $filters,
            'types' => StockMoveType::cases(),
            'products' => $this->products->options(),
            'locations' => $this->locations->options(false),
        ]);
    }

    public function persediaan(Request $request): View
    {
        return view('laporan.persediaan', [
            'products' => $this->products->paginate($this->stockFilters($request), PageSize::TRANSACTIONS),
            'categories' => $this->categories->options(),
            'filters' => $this->stockFilters($request),
            'statuses' => StockStatus::cases(),
        ]);
    }

    public function exportMutasi(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);

        return $this->stream('laporan-mutasi-stok', function () use ($filters): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Waktu', 'Nomor', 'Tipe', 'Produk', 'SKU', 'Lokasi Asal', 'Lokasi Tujuan', 'Jumlah', 'Stok Sebelum', 'Stok Sesudah', 'Dicatat Oleh']);

            foreach ($this->moves->filtered($filters) as $move) {
                fputcsv($handle, [
                    $move->created_at->format('d/m/Y H:i'),
                    $move->reference,
                    $move->typeLabel(),
                    $move->product->name,
                    $move->product->sku,
                    $move->fromLocation?->name ?? '-',
                    $move->toLocation?->name ?? '-',
                    $move->qty,
                    $move->stock_before,
                    $move->stock_after,
                    $move->user?->name ?? '-',
                ]);
            }

            fclose($handle);
        });
    }

    public function exportPersediaan(Request $request): StreamedResponse
    {
        $products = $this->products->paginate($this->stockFilters($request), PageSize::BULK);

        return $this->stream('laporan-persediaan', function () use ($products): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['SKU', 'Produk', 'Kategori', 'Supplier', 'Satuan', 'Stok', 'Stok Minimum', 'Harga', 'Nilai Stok', 'Status']);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->sku,
                    $product->name,
                    $product->category->name,
                    $product->supplier?->name ?? '-',
                    $product->unit,
                    $product->stock,
                    $product->min_stock,
                    $product->price,
                    $product->stockValue(),
                    $product->status()->label(),
                ]);
            }

            fclose($handle);
        });
    }

    public function printMutasi(Request $request): View
    {
        $filters = $this->filters($request);

        return view('laporan.print-mutasi', [
            'moves' => $this->moves->filtered($filters),
            'summary' => $this->moves->summary($filters),
            'filters' => $filters,
        ]);
    }

    /** @return array<string, mixed> */
    private function filters(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString() ?: null,
            'type' => $request->string('type')->toString() ?: null,
            'product_id' => $request->integer('product_id') ?: null,
            'location_id' => $request->integer('location_id') ?: null,
            'from' => $request->date('from')?->toDateString(),
            'to' => $request->date('to')?->toDateString(),
        ];
    }

    /** @return array<string, mixed> */
    private function stockFilters(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString() ?: null,
            'category_id' => $request->integer('category_id') ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'sort' => 'stock',
            'direction' => 'asc',
        ];
    }

    private function stream(string $filename, callable $callback): StreamedResponse
    {
        return response()->streamDownload($callback, $filename.'-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
