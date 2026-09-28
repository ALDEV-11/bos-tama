<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_dapat_masuk_dengan_kredensial_benar(): void
    {
        $user = $this->createUser(RoleName::Admin);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        $user = $this->createUser(RoleName::Admin);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'salahbanget',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $user = $this->createUser(RoleName::Admin, isActive: false);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_pendaftaran_membuat_akun_dengan_role_petugas(): void
    {
        Role::query()->create(['name' => RoleName::Petugas->value, 'label' => 'Petugas']);

        $response = $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::query()->where('email', 'budi@example.test')->firstOrFail();

        $this->assertSame(RoleName::Petugas->value, $user->role_name);
        $this->assertAuthenticatedAs($user);
    }

    public function test_pendaftaran_ditolak_bila_role_petugas_tidak_ada(): void
    {
        $this->post('/register', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $user = $this->createUser(RoleName::Admin);

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
    }

    private function createUser(RoleName $role, bool $isActive = true): User
    {
        $model = Role::factory()->create([
            'name' => $role->value,
            'label' => $role->label(),
        ]);

        return User::factory()->create([
            'role_id' => $model->id,
            'is_active' => $isActive,
            'password' => 'rahasia123',
        ]);
    }
}
