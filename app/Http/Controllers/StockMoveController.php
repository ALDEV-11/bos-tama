<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Enums\StockMoveType;
use App\Models\StockMove;
use App\Support\PageSize;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class StockMoveController extends Controller
{
    public function __construct(
        protected readonly StockMoveRepositoryInterface $moves,
        protected readonly ProductRepositoryInterface $products,
        protected readonly LocationRepositoryInterface $locations,
    ) {}

    abstract protected function type(): StockMoveType;

    abstract protected function routePrefix(): string;

    abstract protected function pageTitle(): string;

    /** Varian menentukan bagian form tambahan (lokasi tujuan, arah retur, hasil opname). */
    protected function variant(): string
    {
        return 'default';
    }

    /** @return array<int, string> */
    protected function types(): array
    {
        return [$this->type()->value];
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'product_id' => $request->integer('product_id') ?: null,
            'location_id' => $request->integer('location_id') ?: null,
            'from' => $request->date('from')?->toDateString(),
            'to' => $request->date('to')?->toDateString(),
        ];

        return view('stok.index', [
            'pageTitle' => $this->pageTitle(),
            'routePrefix' => $this->routePrefix(),
            'variant' => $this->variant(),
            'moves' => $this->moves->paginate([...$filters, 'type' => $this->types()], PageSize::TRANSACTIONS),
            'products' => $this->products->options(),
            'locations' => $this->locations->options(),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('stok.form', [
            'pageTitle' => $this->pageTitle(),
            'routePrefix' => $this->routePrefix(),
            'variant' => $this->variant(),
            'products' => $this->products->options(),
            'locations' => $this->locations->options(),
            'stockMatrix' => $this->locations->stockMatrix(),
        ]);
    }

    /** Dipanggil setelah mutasi berhasil disimpan agar pesan sukses seragam. */
    protected function success(StockMove $move, ?string $message = null): RedirectResponse
    {
        return redirect()
            ->route($this->routePrefix().'.index')
            ->with('success', $message ?? "{$this->pageTitle()} berhasil disimpan dengan nomor {$move->reference}.");
    }
}
