<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use App\Support\PageSize;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaginationLimitTest extends TestCase
{
    use RefreshDatabase;

    /** Tiap produk menghasilkan satu mutasi masuk dan satu mutasi keluar. */
    private const PRODUCTS = 9;

    private const MOVES = self::PRODUCTS * 2;

    /**
     * Halaman yang tabelnya dibatasi 5 baris, beserta jumlah total baris yang diharapkan.
     *
     * @return array<string, array{string, int}>
     */
    public static function limitedPages(): array
    {
        return [
            'mutasi stok' => ['/laporan', self::MOVES],
            'persediaan' => ['/persediaan', self::PRODUCTS],
            'produk' => ['/produk', self::PRODUCTS],
            'stok masuk' => ['/stok-masuk', self::PRODUCTS],
            'stok keluar' => ['/stok-keluar', self::PRODUCTS],
        ];
    }

    #[DataProvider('limitedPages')]
    public function test_halaman_pertama_membatasi_lima_baris(string $path, int $expectedTotal): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $this->seedTransactions($admin);

        $paginator = $this->paginatorFor($this->actingAs($admin)->get($path)->assertOk());

        $this->assertSame(PageSize::TRANSACTIONS, $paginator->perPage());
        $this->assertSame($expectedTotal, $paginator->total());
        $this->assertSame(1, $paginator->currentPage());
        $this->assertSame(PageSize::TRANSACTIONS, $paginator->count());
        $this->assertSame(1, $paginator->firstItem());
        $this->assertSame(5, $paginator->lastItem());
        $this->assertTrue($paginator->hasMorePages());
        $this->assertSame((int) ceil($expectedTotal / PageSize::TRANSACTIONS), $paginator->lastPage());
    }

    #[DataProvider('limitedPages')]
    public function test_halaman_kedua_menampilkan_baris_setelahnya(string $path, int $expectedTotal): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $this->seedTransactions($admin);

        $paginator = $this->paginatorFor($this->actingAs($admin)->get($path.'?page=2')->assertOk());
        $lastItem = min($expectedTotal, 2 * PageSize::TRANSACTIONS);

        $this->assertSame(2, $paginator->currentPage());
        $this->assertSame(6, $paginator->firstItem());
        $this->assertSame($lastItem, $paginator->lastItem());
        $this->assertSame($lastItem - PageSize::TRANSACTIONS, $paginator->count());
        $this->assertSame($expectedTotal > $lastItem, $paginator->hasMorePages());
    }

    #[DataProvider('limitedPages')]
    public function test_tautan_pagination_tampil(string $path, int $expectedTotal): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $this->seedTransactions($admin);

        $this->actingAs($admin)
            ->get($path)
            ->assertOk()
            ->assertSee('pagination__item', false)
            ->assertSee('page=2', false)
            ->assertSee('dari '.$expectedTotal.' data', false);
    }

    public function test_laporan_menjumlahkan_seluruh_baris_lintas_halaman(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $this->seedTransactions($admin);

        $response = $this->actingAs($admin)->get('/laporan')->assertOk();

        $this->assertSame(self::MOVES, $response->viewData('moves')->total());
        $this->assertSame(4, $response->viewData('moves')->lastPage());
        $this->assertSame(self::MOVES, $response->viewData('summary')['trx']);
    }

    public function test_filter_tipe_menyingkapi_ringkasan_seluruh_baris(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $this->seedTransactions($admin);

        $response = $this->actingAs($admin)->get('/laporan?type=masuk')->assertOk();

        $this->assertSame(self::PRODUCTS, $response->viewData('moves')->total());
        $this->assertSame(self::PRODUCTS, $response->viewData('summary')['trx']);
    }

    public function test_tipe_mutasi_tidak_saling_bocor_ke_halaman_lain(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $this->seedTransactions($admin);

        $this->assertSame(self::PRODUCTS, $this->actingAs($admin)->get('/stok-masuk')->viewData('moves')->total());
        $this->assertSame(self::PRODUCTS, $this->actingAs($admin)->get('/stok-keluar')->viewData('moves')->total());
    }

    public function test_unduh_csv_mengunduh_seluruh_baris_bukan_hanya_halaman_pertama(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $this->seedTransactions($admin);

        $csv = $this->actingAs($admin)->get('/laporan/unduh')->assertOk()->streamedContent();

        $this->assertSame(self::MOVES + 1, substr_count(trim($csv), "\n") + 1);
    }

    public function test_tabel_master_data_tetap_berpaginasi_lima_belas_baris(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        Supplier::factory()->count(PageSize::MASTER_DATA + 3)->create();

        $paginator = $this->actingAs($admin)->get('/supplier')->assertOk()->viewData('suppliers');

        $this->assertSame(PageSize::MASTER_DATA, $paginator->perPage());
        $this->assertSame(2, $paginator->lastPage());
        $this->assertSame(PageSize::MASTER_DATA + 3, $paginator->total());
    }

    private function paginatorFor(TestResponse $response): LengthAwarePaginator
    {
        $data = $response->viewData();

        foreach (['moves', 'products'] as $key) {
            if (isset($data[$key])) {
                return $data[$key];
            }
        }

        $this->fail('Halaman tidak mengirim data berpaginasi.');
    }

    private function seedTransactions(User $admin): void
    {
        $category = Category::factory()->create();
        $location = Location::factory()->create();
        $stock = app(StockService::class);

        for ($i = 1; $i <= self::PRODUCTS; $i++) {
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'stock' => 0,
                'name' => 'Produk Paginated '.$i,
            ]);

            $stock->stockIn($product->id, $location->id, $i, $admin);
            $stock->stockOut($product->id, $location->id, 1, $admin);
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
