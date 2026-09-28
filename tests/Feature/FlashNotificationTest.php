<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_aplikasi_mengirim_payload_sweetalert(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin)
            ->from('/produk')
            ->post('/produk', ['name' => ''])
            ->assertRedirect('/produk');

        $this->actingAs($admin)
            ->get('/produk')
            ->assertOk()
            ->assertSee('data-flash', false)
            ->assertSee('"type":"error"', false)
            ->assertDontSee('class="toast"', false);
    }

    public function test_pesan_berhasil_dikirim_sebagai_payload_sweetalert(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin)
            ->post('/kategori', ['name' => 'Wadah'])
            ->assertRedirect('/kategori')
            ->assertSessionHas('success');

        $this->actingAs($admin)
            ->get('/kategori')
            ->assertOk()
            ->assertSee('"type":"success"', false);
    }

    public function test_halaman_tanpa_pesan_tidak_mengirim_payload(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('data-flash', false);
    }

    public function test_halaman_login_tetap_memakai_toast_biasa(): void
    {
        $this->post('/login', ['email' => 'salah@example.com', 'password' => 'rahasiah']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('class="toast"', false)
            ->assertSee('alert--danger', false)
            ->assertDontSee('data-flash', false);
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
