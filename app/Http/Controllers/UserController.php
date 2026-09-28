<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RoleRepositoryInterface $roles,
    ) {}

    public function index(Request $request): View
    {
        return view('pengguna.index', [
            'users' => $this->users->paginate($request->string('search')->toString() ?: null),
            'roles' => $this->roles->options(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->users->create($this->validated($request));

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        if ($user === $request->user()->id && ! $request->boolean('is_active')) {
            return back()->withErrors(['is_active' => 'Anda tidak menonaktifkan akun sendiri.']);
        }

        $this->users->update($user, $this->validated($request, $user));

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, int $user): RedirectResponse
    {
        if ($user === $request->user()->id) {
            return back()->withErrors(['delete' => 'Akun Anda sendiri tidak dapat dihapus.']);
        }

        $this->users->delete($user);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?int $userId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role_id' => ['required', Rule::exists('roles', 'id')],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['phone'] = $data['phone'] ?? null;

        if (($data['password'] ?? '') === '') {
            unset($data['password']);
        }

        return $data;
    }
}
