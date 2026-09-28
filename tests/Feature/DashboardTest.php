<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_menampilkan_lima_kartu_ringkasan(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $content = $this->actingAs($admin)->get('/dashboard')->assertOk()->getContent();

        $this->assertSame(5, substr_count($content, 'stat-card__value'));
        $this->assertStringNotContainsString('Bulan Ini', $content);
    }

    public function test_kartu_ringkasan_menampilkan_lima_angka_yang_dipakai(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        Product::factory()->create(['stock' => 0, 'is_active' => true]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Produk Aktif')
            ->assertSee('Total Unit Stok')
            ->assertSee('Nilai Persediaan')
            ->assertSee('Stok Menipis')
            ->assertSee('Stok Habis');
    }

    public function test_dashboard_menempatkan_konten_dalam_shell_satu_layar(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $content = $this->actingAs($admin)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('class="app app--viewport"', $content);
        $this->assertStringContainsString('class="dashboard"', $content);
        $this->assertStringContainsString('stat-grid dashboard__stats', $content);
        $this->assertSame(2, substr_count($content, 'card card--fill'));
    }

    public function test_halaman_lain_tetap_boleh_menggulir(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin)
            ->get('/produk')
            ->assertOk()
            ->assertDontSee('app--viewport', false);
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
