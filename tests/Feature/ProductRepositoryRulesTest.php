<?php

namespace Tests\Feature;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Enums\RoleName;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_produk_berstok_tidak_bisa_dihapus(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $product = Product::factory()->create(['stock' => 12]);

        $this->actingAs($admin)
            ->from('/produk')
            ->delete('/produk/'.$product->id)
            ->assertRedirect('/produk')
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_produk_tanpa_stok_tetap_bisa_dihapus(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $product = Product::factory()->create(['stock' => 0]);

        $this->actingAs($admin)
            ->delete('/produk/'.$product->id)
            ->assertRedirect('/produk')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_cek_stok_di_repository_menasonic(): void
    {
        $products = app(ProductRepositoryInterface::class);

        $berstok = Product::factory()->create(['stock' => 1]);
        $kosong = Product::factory()->create(['stock' => 0]);

        $this->assertTrue($products->hasStock($berstok->id));
        $this->assertFalse($products->hasStock($kosong->id));
    }

    public function test_menandai_peringatan_memperbarui_waktu_notifikasi(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'last_alerted_at' => null]);

        $this->assertNull($product->last_alerted_at);

        $updated = app(ProductRepositoryInterface::class)->markAlerted($product->id);

        $this->assertNotNull($updated->last_alerted_at);
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
