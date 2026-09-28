<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_terproteksi_mengirim_header_lawan_cache(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();

        $cacheControl = (string) $response->headers->get('Cache-Control');

        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $response->assertHeader('Pragma', 'no-cache');
    }

    public function test_layout_memuat_meta_tag_lawan_cache(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertSee('no-store, no-cache, must-revalidate', escape: false);
    }

    public function test_halaman_tidak_bisa_dibuka_lagi_setelah_logout(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin)->get('/dashboard')->assertOk();

        $this->post('/logout')->assertRedirect('/login');

        $this->assertGuest();

        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/produk')->assertRedirect('/login');
        $this->get('/laporan')->assertRedirect('/login');
    }

    public function test_token_sesi_berubah_setelah_logout(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin);
        $before = session()->getId();

        $this->post('/logout');

        $this->assertNotSame($before, session()->getId());
    }

    private function createUser(RoleName $role): User
    {
        $model = Role::factory()->create([
            'name' => $role->value,
            'label' => $role->label(),
        ]);

        return User::factory()->create(['role_id' => $model->id]);
    }
}
