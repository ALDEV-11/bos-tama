<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\SupplierRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierRepositoryInterface $suppliers) {}

    public function index(Request $request): View
    {
        return view('master.supplier.index', [
            'suppliers' => $this->suppliers->paginate($request->string('search')->toString() ?: null),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->suppliers->create($this->validated($request));

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, int $supplier): RedirectResponse
    {
        $this->suppliers->update($supplier, $this->validated($request));

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(int $supplier): RedirectResponse
    {
        if ($this->suppliers->isInUse($supplier)) {
            return back()->withErrors(['delete' => 'Supplier masih dipakai produk. Pindahkan produknya terlebih dahulu.']);
        }

        $this->suppliers->delete($supplier);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
