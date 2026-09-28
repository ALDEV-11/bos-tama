<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryRepositoryInterface $categories) {}

    public function index(Request $request): View
    {
        return view('master.kategori.index', [
            'categories' => $this->categories->paginate($request->string('search')->toString() ?: null),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->categories->create($request->validate($this->rules()));

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, int $category): RedirectResponse
    {
        $this->categories->update($category, $request->validate($this->rules($category)));

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(int $category): RedirectResponse
    {
        if ($this->categories->isInUse($category)) {
            return back()->withErrors(['delete' => 'Kategori masih dipakai produk. Pindahkan produknya terlebih dahulu.']);
        }

        $this->categories->delete($category);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    private function rules(?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($ignoreId)],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
