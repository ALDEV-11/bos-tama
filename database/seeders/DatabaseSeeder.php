<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::query()->create(['name' => RoleName::Admin->value, 'label' => RoleName::Admin->label()]);
        $petugasRole = Role::query()->create(['name' => RoleName::Petugas->value, 'label' => RoleName::Petugas->label()]);

        User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin@inventori.test',
            'phone' => '081234567890',
            'role_id' => $adminRole->id,
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        User::query()->create([
            'name' => 'Petugas Gudang',
            'email' => 'petugas@inventori.test',
            'phone' => '081298765432',
            'role_id' => $petugasRole->id,
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $categories = collect(['Wadah', 'Instrument', 'Kosmetik', 'Aksesori'])
            ->mapWithKeys(fn (string $name) => [$name => Category::query()->create(['name' => $name])]);

        $suppliers = collect(['PT Vasco Kemasan', 'CV Berkah Niaga', 'PT Sentosa Prima'])
            ->mapWithKeys(fn (string $name) => [$name => Supplier::query()->create([
                'name' => $name,
                'contact_name' => fake()->name(),
                'phone' => fake()->numerify('08##########'),
                'email' => fake()->unique()->companyEmail(),
                'address' => fake()->address(),
                'is_active' => true,
            ])]);

        $locations = collect([
            ['TKO-01', 'Toko Pusat'],
            ['TKO-02', 'Gudang Utama'],
        ])->mapWithKeys(fn (array $row) => [$row[0] => Location::query()->create([
            'code' => $row[0],
            'name' => $row[1],
            'is_active' => true,
        ])]);

        $catalog = [
            ['VSC-001', 'Toples Bowl 500 ml', 'Wadah', 'PT Vasco Kemasan', 'pcs', 12_000, 20, 140],
            ['VSC-002', 'Toples Kaca 250 ml', 'Wadah', 'PT Vasco Kemasan', 'pcs', 9_500, 25, 96],
            ['VSC-003', 'Botol Dispensing 60 ml', 'Wadah', 'PT Vasco Kemasan', 'pcs', 14_000, 15, 8],
            ['VSC-004', 'Kotak Kosmetik Zipper', 'Kosmetik', 'CV Berkah Niaga', 'pcs', 22_000, 12, 34],
            ['VSC-005', 'Kuas Makeup Set', 'Aksesori', 'PT Sentosa Prima', 'set', 35_000, 10, 5],
            ['VSC-006', 'Cermin Ria Lipat', 'Aksesori', 'PT Sentosa Prima', 'pcs', 18_500, 8, 0],
            ['VSC-007', 'Pinset Rambut', 'Aksesori', 'PT Sentosa Prima', 'pcs', 7_000, 20, 62],
            ['VSC-008', 'Spons Riasa', 'Kosmetik', 'CV Berkah Niaga', 'pcs', 5_500, 30, 41],
        ];

        $openingActor = User::query()->where('email', 'admin@inventori.test')->firstOrFail();
        $openingLocation = $locations['TKO-02'];
        $stock = app(StockService::class);

        foreach ($catalog as [$sku, $name, $category, $supplier, $unit, $price, $minStock, $openingQty]) {
            $product = Product::query()->create([
                'sku' => $sku,
                'name' => $name,
                'category_id' => $categories[$category]->id,
                'supplier_id' => $suppliers[$supplier]->id,
                'unit' => $unit,
                'price' => $price,
                'min_stock' => $minStock,
                'is_active' => true,
            ]);

            if ($openingQty > 0) {
                $stock->stockIn(
                    productId: $product->id,
                    locationId: $openingLocation->id,
                    qty: $openingQty,
                    actor: $openingActor,
                    reference: 'MKM/'.$openingLocation->code.'/AWAL/'.$sku,
                    note: 'Stok awal saat pencatatan katalog.',
                );
            }
        }

        $this->command?->newLine();
        $this->command?->info('Data awal siap. Login admin@inventori.test / password');
    }
}
