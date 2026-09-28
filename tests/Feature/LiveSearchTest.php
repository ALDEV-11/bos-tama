<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LiveSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Halaman yang-search-nya diikat script live search.
     *
     * @return array<string, array{string}>
     */
    public static function searchPages(): array
    {
        return [
            'produk' => ['/produk'],
            'stok masuk' => ['/stok-masuk'],
            'stok keluar' => ['/stok-keluar'],
            'transfer' => ['/transfer'],
            'retur' => ['/retur'],
            'opname' => ['/opname'],
            'laporan mutasi' => ['/laporan'],
            'persediaan' => ['/persediaan'],
            'kategori' => ['/kategori'],
            'supplier' => ['/supplier'],
            'lokasi' => ['/lokasi'],
            'pengguna' => ['/pengguna'],
        ];
    }

    #[DataProvider('searchPages')]
    public function test_halaman_menyediakan_kontrak_search_live(string $path): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $category = Category::factory()->create();

        Product::factory()->create(['category_id' => $category->id]);
        Location::factory()->create();

        $this->actingAs($admin)
            ->get($path)
            ->assertOk()
            ->assertSee('<div class="filters__search">', false)
            ->assertSee('name="search"', false)
            ->assertSee('type="search"', false);
    }

    public function test_navbar_tidak_lagi_merender_searchbox(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $category = Category::factory()->create();

        Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($admin)
            ->get('/produk')
            ->assertOk()
            ->assertDontSee('topbar__search', false)
            ->assertDontSee('Cari produk (SKU atau nama)', false)
            ->assertSee('class="topbar"', false);
    }

    public function test_query_search_menyaring_produk_pada_sku_dan_nama(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $category = Category::factory()->create();

        Product::factory()->create(['category_id' => $category->id, 'sku' => 'KBL-001', 'name' => 'Kabel Jaring']);
        Product::factory()->create(['category_id' => $category->id, 'sku' => 'MTR-002', 'name' => 'Motor Listrik']);

        $this->actingAs($admin)
            ->get('/produk?search=KBL-001')
            ->assertOk()
            ->assertSee('Kabel Jaring')
            ->assertDontSee('Motor Listrik');

        $this->actingAs($admin)
            ->get('/produk?search=Motor')
            ->assertOk()
            ->assertSee('Motor Listrik')
            ->assertDontSee('Kabel Jaring');
    }

    public function test_query_kosong_menampilkan_seluruh_produk(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $category = Category::factory()->create();

        Product::factory()->create(['category_id' => $category->id, 'name' => 'Kabel Jaring']);
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Motor Listrik']);

        $this->actingAs($admin)
            ->get('/produk?search=')
            ->assertOk()
            ->assertSee('Kabel Jaring')
            ->assertSee('Motor Listrik');
    }

    public function test_nilai_search_terakhir_ditampilkan_kembali_di_input(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $category = Category::factory()->create();

        Product::factory()->create(['category_id' => $category->id, 'name' => 'Kabel Jaring']);

        $this->actingAs($admin)
            ->get('/produk?search=Kabel')
            ->assertOk()
            ->assertSee('value="Kabel"', false);
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
