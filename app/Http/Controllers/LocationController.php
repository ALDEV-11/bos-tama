<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\LocationRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function __construct(private readonly LocationRepositoryInterface $locations) {}

    public function index(Request $request): View
    {
        return view('master.lokasi.index', [
            'locations' => $this->locations->paginate($request->string('search')->toString() ?: null),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->locations->create($this->validated($request));

        return redirect()->route('lokasi.index')->with('success', 'Lokasi penyimpanan berhasil ditambahkan.');
    }

    public function update(Request $request, int $location): RedirectResponse
    {
        $this->locations->update($location, $this->validated($request, $location));

        return redirect()->route('lokasi.index')->with('success', 'Lokasi penyimpanan berhasil diperbarui.');
    }

    public function destroy(int $location): RedirectResponse
    {
        $this->locations->delete($location);

        return redirect()->route('lokasi.index')->with('success', 'Lokasi penyimpanan berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('locations', 'code')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
