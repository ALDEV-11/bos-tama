<?php

namespace Tests\Feature;

use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Enums\RoleName;
use App\Enums\StockMoveType;
use App\Models\Location;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockMove;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_stok_masuk_menambah_stok_total_dan_per_lokasi(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 0]);
        $location = Location::factory()->create();

        $this->actingAs($actor)
            ->post('/stok-masuk', [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'qty' => 25,
            ])
            ->assertRedirect('/stok-masuk')
            ->assertSessionHas('success');

        $this->assertSame(25, $product->fresh()->stock);
        $this->assertSame(25, $product->locationStocks()->where('location_id', $location->id)->value('qty'));

        $move = StockMove::query()->latest('id')->firstOrFail();

        $this->assertSame(StockMoveType::In, $move->type);
        $this->assertSame(0, $move->stock_before);
        $this->assertSame(25, $move->stock_after);
    }

    public function test_stok_keluar_melebihi_stok_ditolak(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 4]);
        $location = Location::factory()->create();

        $product->locationStocks()->create(['location_id' => $location->id, 'qty' => 4]);

        $this->actingAs($actor)
            ->from('/stok-keluar/tambah')
            ->post('/stok-keluar', [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'qty' => 10,
            ])
            ->assertSessionHasErrors();

        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(0, StockMove::query()->count());
    }

    public function test_transfer_membuat_dua_baris_ledger(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 30]);
        $origin = Location::factory()->create();
        $destination = Location::factory()->create();

        $product->locationStocks()->create(['location_id' => $origin->id, 'qty' => 30]);

        $this->actingAs($actor)
            ->post('/transfer', [
                'product_id' => $product->id,
                'from_location_id' => $origin->id,
                'to_location_id' => $destination->id,
                'qty' => 12,
            ])
            ->assertRedirect('/transfer');

        $this->assertSame(30, $product->fresh()->stock, 'Transfer tidak boleh mengubah total stok.');
        $this->assertSame(18, $product->locationStocks()->where('location_id', $origin->id)->value('qty'));
        $this->assertSame(12, $product->locationStocks()->where('location_id', $destination->id)->value('qty'));

        $moves = StockMove::query()->orderBy('id')->get();

        $this->assertCount(2, $moves);
        $this->assertSame($moves[0]->reference, $moves[1]->reference);
        $this->assertSame($moves[1]->id, $moves[0]->related_move_id);
    }

    public function test_lokasi_asal_dan_tujuan_transfer_tidak_boleh_sama(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 10]);
        $location = Location::factory()->create();

        $this->actingAs($actor)
            ->post('/transfer', [
                'product_id' => $product->id,
                'from_location_id' => $location->id,
                'to_location_id' => $location->id,
                'qty' => 2,
            ])
            ->assertSessionHasErrors('to_location_id');
    }

    public function test_opname_mencatat_selisih(): void
    {
        $actor = $this->createUser(RoleName::Admin);
        $product = Product::factory()->create(['stock' => 10]);
        $location = Location::factory()->create();

        $product->locationStocks()->create(['location_id' => $location->id, 'qty' => 10]);

        $this->actingAs($actor)
            ->post('/opname', [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'physical_qty' => 7,
            ])
            ->assertRedirect('/opname');

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_opname_hanya_untuk_admin(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 10]);
        $location = Location::factory()->create();

        $this->actingAs($actor)
            ->post('/opname', [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'physical_qty' => 5,
            ])
            ->assertForbidden();

        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_stok_keluar_mengurangi_total_produk_dan_stok_lokasi(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 30]);
        $location = Location::factory()->create();

        $product->locationStocks()->create(['location_id' => $location->id, 'qty' => 30]);

        $this->actingAs($actor)
            ->post('/stok-keluar', [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'qty' => 12,
            ])
            ->assertRedirect('/stok-keluar')
            ->assertSessionHas('success');

        $this->assertSame(18, $product->fresh()->stock);
        $this->assertSame(18, $product->locationStocks()->where('location_id', $location->id)->value('qty'));
    }

    public function test_pesan_stok_tidak_cukup_menyebut_nama_lokasi(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 4]);
        $location = Location::factory()->create(['name' => 'Gudang Cadangan']);

        $product->locationStocks()->create(['location_id' => $location->id, 'qty' => 2]);

        $this->actingAs($actor)
            ->from('/stok-keluar/tambah')
            ->post('/stok-keluar', [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'qty' => 9,
            ])
            ->assertSessionHasErrors('qty');

        $this->assertStringContainsString('Gudang Cadangan', session('errors')->first('qty'));
        $this->assertStringContainsString('Tersedia 2', session('errors')->first('qty'));
    }

    public function test_stok_keluar_menolak_lokasi_yang_stoknya_kosong_walaupun_total_cukup(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 50]);
        $origin = Location::factory()->create();
        $empty = Location::factory()->create(['name' => 'Etalase Depan']);

        $product->locationStocks()->create(['location_id' => $origin->id, 'qty' => 50]);

        $this->actingAs($actor)
            ->from('/stok-keluar/tambah')
            ->post('/stok-keluar', [
                'product_id' => $product->id,
                'location_id' => $empty->id,
                'qty' => 1,
            ])
            ->assertSessionHasErrors('qty');

        $this->assertSame(50, $product->fresh()->stock);
    }

    public function test_perbaikan_menyeimbangkan_stok_awal_yang_tidak_terlacak(): void
    {
        $product = Product::factory()->create(['stock' => 40]);
        $location = Location::factory()->create(['code' => 'TKO-02']);

        $this->assertSame(0, $product->locationStocks()->sum('qty'));

        $this->backfillLocationStocks();

        $this->assertSame(40, $product->locationStocks()->where('location_id', $location->id)->value('qty'));
        $this->assertSame(40, $product->locationStocks()->sum('qty'));
    }

    public function test_perbaikan_menambah_selisih_tanpa_menimpa_stok_lokasi_lama(): void
    {
        $product = Product::factory()->create(['stock' => 18]);
        $target = Location::factory()->create(['code' => 'TKO-02']);
        $other = Location::factory()->create();

        $product->locationStocks()->create(['location_id' => $target->id, 'qty' => 10]);

        $this->backfillLocationStocks();

        $this->assertSame(18, $product->locationStocks()->where('location_id', $target->id)->value('qty'));
        $this->assertFalse($product->locationStocks()->where('location_id', $other->id)->exists());
        $this->assertSame(18, $product->locationStocks()->sum('qty'));
    }

    public function test_stok_keluar_berjalan_setelah_stok_awal_diseimbangkan(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 140]);
        $location = Location::factory()->create(['code' => 'TKO-02']);

        $this->backfillLocationStocks();

        $this->actingAs($actor)
            ->post('/stok-keluar', [
                'product_id' => $product->id,
                'location_id' => $location->id,
                'qty' => 25,
            ])
            ->assertRedirect('/stok-keluar')
            ->assertSessionHas('success');

        $this->assertSame(115, $product->fresh()->stock);
        $this->assertSame(115, $product->locationStocks()->where('location_id', $location->id)->value('qty'));
    }

    public function test_form_mutasi_menampilkan_stok_per_lokasi_produk_terpilih(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 30]);
        $gudang = Location::factory()->create(['code' => 'TKO-02']);
        $toko = Location::factory()->create(['code' => 'TKO-01']);

        $product->locationStocks()->create(['location_id' => $gudang->id, 'qty' => 30]);

        $this->actingAs($actor)
            ->from('/stok-keluar/tambah')
            ->post('/stok-keluar', [
                'product_id' => $product->id,
                'location_id' => $toko->id,
                'qty' => 999,
            ])
            ->assertRedirect('/stok-keluar/tambah');

        $this->actingAs($actor)
            ->get('/stok-keluar/tambah')
            ->assertOk()
            ->assertDontSee('availability--empty', false)
            ->assertSee('Total 30', false)
            ->assertSee('Stok tersedia per lokasi');
    }

    public function test_form_mutasi_menuntut_pilihan_produk_sebelum_menampilkan_stok(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 30]);
        $gudang = Location::factory()->create(['code' => 'TKO-02']);

        $product->locationStocks()->create(['location_id' => $gudang->id, 'qty' => 30]);

        $this->actingAs($actor)
            ->get('/stok-keluar/tambah')
            ->assertOk()
            ->assertSee('availability--empty', false)
            ->assertSee('data-stock-availability', false)
            ->assertSee('Pilih produk dulu untuk melihat stok di tiap lokasi.');
    }

    public function test_panel_stok_per_lokasi_menampilkan_seluruh_lokasi_termasuk_yang_kosong(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 30]);
        $gudang = Location::factory()->create(['code' => 'TKO-02']);
        $toko = Location::factory()->create(['code' => 'TKO-01']);

        $product->locationStocks()->create(['location_id' => $gudang->id, 'qty' => 30]);

        $matrix = app(LocationRepositoryInterface::class)->stockMatrix();

        $this->assertSame([$gudang->id => 30], $matrix[$product->id]);
        $this->assertArrayNotHasKey($toko->id, $matrix[$product->id] ?? []);

        $this->actingAs($actor)
            ->get('/stok-keluar/tambah')
            ->assertOk()
            ->assertSee('data-location="'.$gudang->id.'"', false)
            ->assertSee('data-location="'.$toko->id.'"', false);
    }

    public function test_mutasi_mencatat_pengguna_pencatat(): void
    {
        $actor = $this->createUser(RoleName::Petugas);
        $product = Product::factory()->create(['stock' => 0]);
        $location = Location::factory()->create();

        app(StockService::class)->stockIn($product->id, $location->id, 5, $actor);

        $this->assertSame($actor->id, StockMove::query()->firstOrFail()->user_id);
    }

    private function createUser(RoleName $role): User
    {
        $model = Role::factory()->create([
            'name' => $role->value,
            'label' => $role->label(),
        ]);

        return User::factory()->create(['role_id' => $model->id]);
    }

    private function backfillLocationStocks(): void
    {
        $path = collect(glob(database_path('migrations/*_backfill_location_stocks_from_product_stock.php')))->first();

        $this->assertNotNull($path, 'Migration perbaikan location_stocks tidak ditemukan.');

        (require $path)->up();
    }
}
