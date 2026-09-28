<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Category;
use App\Models\Location;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MasterDataModalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function masterDataPages(): array
    {
        return [
            'kategori' => ['/kategori', 'modal-kategori', 'Tambah Kategori'],
            'lokasi' => ['/lokasi', 'modal-lokasi', 'Tambah Lokasi'],
            'supplier' => ['/supplier', 'modal-supplier', 'Tambah Supplier'],
            'pengguna' => ['/pengguna', 'modal-pengguna', 'Tambah Pengguna'],
        ];
    }

    #[DataProvider('masterDataPages')]
    public function test_halaman_master_data_menyediakan_modal_tambah(string $path, string $modalId, string $label): void
    {
        $admin = $this->createUser(RoleName::Admin);
        Role::factory()->create(['name' => RoleName::Petugas->value, 'label' => 'Petugas Gudang']);

        $this->actingAs($admin)
            ->get($path)
            ->assertOk()
            ->assertSee('data-modal-open="'.$modalId.'"', false)
            ->assertSee('id="'.$modalId.'"', false)
            ->assertSee('class="modal modal--form"', false)
            ->assertSee($label)
            ->assertSee('data-modal-close', false)
            ->assertDontSee('grid-master', false);
    }

    #[DataProvider('masterDataPages')]
    public function test_modal_tidak_terbuka_otomatis_saat_halaman_dimuat(string $path): void
    {
        $admin = $this->createUser(RoleName::Admin);
        Role::factory()->create(['name' => RoleName::Petugas->value, 'label' => 'Petugas Gudang']);

        $this->actingAs($admin)
            ->get($path)
            ->assertOk()
            ->assertDontSee('<dialog class="modal modal--form" id="modal-kategori" open', false)
            ->assertDontSee('data-modal-open-on-load', false);
    }

    public function test_kategori_baru_dibuat_dari_modal(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin)
            ->post('/kategori', ['name' => 'Alat Tulis', 'description' => 'Kebutuhan kantor'])
            ->assertRedirect('/kategori')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Alat Tulis']);
    }

    public function test_lokasi_baru_dibuat_dari_modal(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin)
            ->post('/lokasi', ['code' => 'TKO-09', 'name' => 'Gudang Cadangan', 'is_active' => '1'])
            ->assertRedirect('/lokasi')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('locations', ['code' => 'TKO-09', 'is_active' => true]);
    }

    public function test_supplier_baru_dibuat_dari_modal(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin)
            ->post('/supplier', ['name' => 'PT S sourced Niaga', 'email' => 'sales@sumber.co.id'])
            ->assertRedirect('/supplier')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', ['name' => 'PT S sourced Niaga']);
    }

    public function test_pengguna_baru_dibuat_dari_modal(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $role = Role::factory()->create(['name' => RoleName::Petugas->value, 'label' => 'Petugas Gudang']);

        $this->actingAs($admin)
            ->post('/pengguna', [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'role_id' => $role->id,
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'is_active' => '1',
            ])
            ->assertRedirect('/pengguna')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'budi@example.com', 'role_id' => $role->id]);
    }

    /**
     * `assertSessionHasErrors()` payloads Consumed flash, jadi error diverifikasi lewat payload SweetAlert2 di halaman tujuan.
     */
    #[DataProvider('masterDataPages')]
    public function test_validasi_gagal_menampilkan_toast_dan_tidak_membuka_modal(string $path): void
    {
        $admin = $this->createUser(RoleName::Admin);
        Role::factory()->create(['name' => RoleName::Petugas->value, 'label' => 'Petugas Gudang']);

        $this->actingAs($admin)
            ->from($path)
            ->post($path, [])
            ->assertRedirect($path);

        $this->actingAs($admin)
            ->get($path)
            ->assertOk()
            ->assertSee('data-flash', false)
            ->assertSee('Periksa kembali isian Anda.')
            ->assertSee('"type":"error"', false);
    }

    public function test_kategori_gagal_ditambahkan_tidak_menambah_data(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $category = Category::factory()->create(['name' => 'Wadah Kaca']);

        $this->actingAs($admin)
            ->from('/kategori')
            ->post('/kategori', ['name' => $category->name])
            ->assertRedirect('/kategori')
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Category::query()->count());
    }

    public function test_edit_inline_tetap_berfungsi_setelah_kartu_tambah_dijadi_modal(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $location = Location::factory()->create(['name' => 'Gudang Lama']);

        $this->actingAs($admin)
            ->put('/lokasi/'.$location->id, ['code' => $location->code, 'name' => 'Gudang Baru'])
            ->assertRedirect('/lokasi')
            ->assertSessionHas('success');

        $this->assertSame('Gudang Baru', $location->fresh()->name);
    }

    public function test_pengguna_yang_bukan_admin_tidak_bisa_membuka_halaman_master(): void
    {
        $petugas = $this->createUser(RoleName::Petugas);

        $this->actingAs($petugas)->get('/kategori')->assertForbidden();
        $this->actingAs($petugas)->get('/lokasi')->assertForbidden();
        $this->actingAs($petugas)->get('/supplier')->assertForbidden();
        $this->actingAs($petugas)->get('/pengguna')->assertForbidden();
    }

    public function test_toast_menampilkan_pesan_validasi_khusus_saat_kode_lokasi_duplikat(): void
    {
        $admin = $this->createUser(RoleName::Admin);
        $location = Location::factory()->create(['code' => 'TKO-03']);

        $this->actingAs($admin)
            ->from('/lokasi')
            ->post('/lokasi', ['code' => $location->code, 'name' => 'Duplikat'])
            ->assertRedirect('/lokasi');

        $this->actingAs($admin)
            ->get('/lokasi')
            ->assertOk()
            ->assertSee('data-flash', false)
            ->assertSee('The code has already been taken.');
    }

    public function test_supplier_dan_kategori_boleh_dibuat_lewat_endpoint_yang_sama(): void
    {
        $admin = $this->createUser(RoleName::Admin);

        $this->actingAs($admin)->post('/supplier', ['name' => 'PT Maju']);
        $this->actingAs($admin)->post('/kategori', ['name' => 'Peralatan']);

        $this->assertDatabaseHas('suppliers', ['name' => 'PT Maju']);
        $this->assertDatabaseHas('categories', ['name' => 'Peralatan']);
        $this->assertSame(1, Supplier::query()->count());
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
