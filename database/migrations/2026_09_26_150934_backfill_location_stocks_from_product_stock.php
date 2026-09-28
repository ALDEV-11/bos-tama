<?php

use App\Models\Location;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Lokasi tujuan untuk stok awal yang belum terlacak per lokasi. */
    private const TARGET_LOCATION_CODE = 'TKO-02';

    /**
     * Stok awal pernah ditulis langsung ke `products.stock` tanpa baris `location_stocks`,
     * sehingga guard pemeriksa stok keluar selalu membaca 0. Selisih antara total produk
     * dan total per lokasi dipindahkan ke lokasi tujuan agar kedua angka sinkron lagi.
     */
    public function up(): void
    {
        $location = Location::query()
            ->where('code', self::TARGET_LOCATION_CODE)
            ->first() ?? Location::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if (! $location) {
            return;
        }

        Product::query()->eachById(function (Product $product) use ($location): void {
            $tracked = (int) DB::table('location_stocks')
                ->where('product_id', $product->id)
                ->sum('qty');

            $missing = $product->stock - $tracked;

            if ($missing <= 0) {
                return;
            }

            $existing = DB::table('location_stocks')
                ->where('product_id', $product->id)
                ->where('location_id', $location->id)
                ->value('qty');

            DB::table('location_stocks')->updateOrInsert(
                ['product_id' => $product->id, 'location_id' => $location->id],
                ['qty' => (int) $existing + $missing],
            );
        });
    }

    /**
     * Perbaikan data sekali jalan: setelah migration ini stok sudah bergerak lagi, jadi
     * tidak ada cara menentukan dengan aman selisih mana yang milik migration ini.
     */
    public function down(): void
    {
        //
    }
};
