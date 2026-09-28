<?php

namespace Tests\Feature;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Enums\RoleName;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Notifications\StockOutNotification;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_menerima_notifikasi_saat_stok_menembus_batas_minimum(): void
    {
        Notification::fake();

        $admin = $this->createUser(RoleName::Admin);
        $product = Product::factory()->create(['stock' => 6, 'min_stock' => 5]);
        $location = Location::factory()->create();

        $product->locationStocks()->create(['location_id' => $location->id, 'qty' => 6]);

        app(StockService::class)->stockOut($product->id, $location->id, 2, $admin);

        Notification::assertSentTo($admin, LowStockNotification::class);
        $this->assertNotNull($product->fresh()->last_alerted_at);
    }

    public function test_tidak_ada_notifikasi_berulang_selama_stok_tetap_menipis(): void
    {
        Notification::fake();

        $admin = $this->createUser(RoleName::Admin);
        $product = Product::factory()->create(['stock' => 6, 'min_stock' => 5]);
        $location = Location::factory()->create();

        $product->locationStocks()->create(['location_id' => $location->id, 'qty' => 6]);
        $stock = app(StockService::class);

        $stock->stockOut($product->id, $location->id, 1, $admin);
        $stock->stockOut($product->id, $location->id, 1, $admin);

        Notification::assertSentToTimes($admin, LowStockNotification::class, 1);
    }

    public function test_notifikasi_stok_habis_dikirim(): void
    {
        Notification::fake();

        $admin = $this->createUser(RoleName::Admin);
        $product = Product::factory()->create(['stock' => 1, 'min_stock' => 5]);
        $location = Location::factory()->create();

        $product->locationStocks()->create(['location_id' => $location->id, 'qty' => 1]);

        app(StockService::class)->stockOut($product->id, $location->id, 1, $admin);

        $this->assertSame(0, $product->fresh()->stock);

        Notification::assertSentTo($admin, StockOutNotification::class);
    }

    public function test_laporan_menampilkan_ringkasan_mutasi(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $product = Product::factory()->create(['stock' => 0]);
        $location = Location::factory()->create();

        $stock = app(StockService::class);
        $stock->stockIn($product->id, $location->id, 20, $admin);
        $stock->stockOut($product->id, $location->id, 8, $admin);

        $this->actingAs($admin)
            ->get('/laporan')
            ->assertOk()
            ->assertSee('Laporan Mutasi Stok');
    }

    public function test_ekspor_csv_menghasilkan_berkas(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $product = Product::factory()->create(['stock' => 0]);
        $location = Location::factory()->create();

        app(StockService::class)->stockIn($product->id, $location->id, 4, $admin);

        $response = $this->actingAs($admin)->get('/laporan/unduh');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_halaman_paginasi_dan_produk_dapat_diakses(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $category = Category::factory()->create();

        Product::factory()->count(20)->create(['category_id' => $category->id]);

        $this->actingAs($admin)->get('/produk')->assertOk()->assertSee('Produk');
        $this->actingAs($admin)->get('/produk?status=aman')->assertOk();
        $this->actingAs($admin)->get('/persediaan')->assertOk();
        $this->actingAs($admin)->get('/notifikasi')->assertOk();
        $this->actingAs($admin)->get('/dashboard')->assertOk();
    }

    public function test_grafik_dashboard_menampilkan_mutasi_tujuh_hari_terakhir(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $product = Product::factory()->create(['stock' => 0]);
        $location = Location::factory()->create();

        $stock = app(StockService::class);
        $stock->stockIn($product->id, $location->id, 12, $admin);
        $stock->stockOut($product->id, $location->id, 5, $admin);

        $series = app(DashboardRepositoryInterface::class)->dailyMovement();

        $this->assertCount(7, $series);

        $today = $series[now()->toDateString()];

        $this->assertSame(12, $today['in']);
        $this->assertSame(5, $today['out']);

        $this->actingAs($admin)->get('/dashboard')->assertOk();
    }

    public function test_dashboard_menampilkan_mutasi_terbaru_dan_menyembunyikan_empty_state(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $product = Product::factory()->create(['stock' => 0]);
        $location = Location::factory()->create();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Belum ada mutasi stok');

        app(StockService::class)->stockIn($product->id, $location->id, 7, $admin);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Belum ada mutasi stok')
            ->assertSee($product->name);
    }

    public function test_dashboard_membatasi_mutasi_terbaru_lima_data(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $category = Category::factory()->create();
        $location = Location::factory()->create();

        $stock = app(StockService::class);

        for ($i = 1; $i <= 8; $i++) {
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'stock' => 0,
                'name' => 'Produk Mutasi '.$i,
            ]);

            $stock->stockIn($product->id, $location->id, $i, $admin);
        }

        $all = app(StockMoveRepositoryInterface::class)->latest(8);
        $references = $all->take(5)->pluck('reference');

        $this->assertCount(5, $references);
        $this->assertCount(8, $all);

        $content = $this->actingAs($admin)->get('/dashboard')->assertOk()->getContent();

        foreach ($references as $reference) {
            $this->assertStringContainsString($reference, $content);
        }

        foreach ($all->reverse()->take(3) as $move) {
            $this->assertStringNotContainsString($move->reference, $content);
        }
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
