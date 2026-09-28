<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Contracts\Repositories\SupplierRepositoryInterface;
use App\Enums\StockStatus;
use App\Support\PageSize;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly StockMoveRepositoryInterface $moves,
        private readonly CategoryRepositoryInterface $categories,
        private readonly SupplierRepositoryInterface $suppliers,
        private readonly LocationRepositoryInterface $locations,
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'category_id' => $request->integer('category_id') ?: null,
            'status' => $request->string('status')->toString() ?: null,
            'is_active' => $request->string('is_active')->toString(),
            'sort' => $request->string('sort')->toString() ?: 'name',
            'direction' => $request->string('direction')->toString() ?: 'asc',
        ];

        return view('produk.index', [
            'products' => $this->products->paginate($filters, PageSize::TRANSACTIONS),
            'categories' => $this->categories->options(),
            'filters' => $filters,
            'statuses' => StockStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('produk.form', [
            'product' => null,
            'categories' => $this->categories->options(),
            'suppliers' => $this->suppliers->options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->products->create($this->validated($request));

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(int $product): View
    {
        $item = $this->products->findOrFail($product);

        return view('produk.show', [
            'product' => $item,
            'moves' => $this->moves->paginate(['product_id' => $item->id], 10),
        ]);
    }

    public function edit(int $product): View
    {
        return view('produk.form', [
            'product' => $this->products->findOrFail($product),
            'categories' => $this->categories->options(),
            'suppliers' => $this->suppliers->options(),
        ]);
    }

    public function update(Request $request, int $product): RedirectResponse
    {
        $this->products->update($product, $this->validated($request, $product));

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(int $product): RedirectResponse
    {
        if ($this->products->hasStock($product)) {
            return back()->withErrors(['delete' => 'Produk masih memiliki stok. Kosongkan stok sebelum menghapus.']);
        }

        $this->products->delete($product);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?int $productId = null): array
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:30', Rule::unique('products', 'sku')->ignore($productId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')],
            'unit' => ['required', 'string', 'max:20'],
            'price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['supplier_id'] = $data['supplier_id'] ?? null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
