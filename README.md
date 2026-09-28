# Sistem Inventori & Notifikasi Stok

Aplikasi inventori untuk katalog Vascon. Satu produk bisa tercatat di beberapa lokasi, setiap perubahan stok disimpan sebagai mutasi, dan administrator diberi tahu begitu stok menyentuh batas minimum.

## Fitur

**Dashboard**
Jumlah produk aktif, total stok, nilai persediaan, produk stok menipis, dan produk stok habis. Dilengkapi grafik pergerakan masuk dan keluar tujuh hari terakhir, daftar produk yang perlu perhatian, serta mutasi terbaru.

**Katalog**
Produk, kategori, supplier, dan lokasi. Produk punya SKU, satuan, harga, dan stok minimum sebagai ambang peringatan. Halaman produk menyediakan pencarian langsung dan rincian stok per lokasi.

**Transaksi stok**

| Modul | Prefix nomor | Keterangan |
| --- | --- | --- |
| Stok Masuk | `MKM` | Barang datang dari supplier |
| Stok Keluar | `KLR` | Barang keluar untuk penjualan atau pemakaian |
| Transfer Lokasi | `TRF` | Perpindahan antar lokasi, dicatat sebagai dua baris berpasangan |
| Retur | `RTM` / `RTK` | Retur dari pelanggan atau retur ke supplier |
| Opname | `PNY` | Penyesuaian stok terhadap hasil hitung fisik |

Nomor mutasi berformat `PREFIX/YYYY/MM/0001`, misalnya `MKM/2026/09/0001`.

**Notifikasi stok**
Administrator menerima notifikasi saat status stok berubah menjadi Menipis atau Habis. Notifikasi tidak dikirim ulang pada transaksi berikutnya selama status tidak berubah, sehingga satu produk tidak menumpuk notifikasi. Lonceng di navbar menampilkan jumlah notifikasi yang belum dibaca.

**Laporan**
Dua laporan, keduanya bisa disaring dan diekspor sebagai CSV.

- Mutasi stok: saring menurut kata kunci, tipe mutasi, produk, lokasi, dan rentang tanggal. Tersedia versi cetak.
- Persediaan: saring menurut kata kunci, kategori, dan status stok.

## Hak Akses

Dua peran, dibatasi lewat middleware `role`.

| Modul | Administrator | Petugas Gudang |
| --- | --- | --- |
| Dashboard, Produk, Notifikasi | Lihat | Lihat |
| Stok masuk, keluar, transfer, retur | Catat | Catat |
| Laporan mutasi dan persediaan | Lihat | Lihat |
| Opname stok | Kelola | Tidak ada akses |
| Kategori, supplier, lokasi, pengguna | Kelola | Tidak ada akses |

Pendaftaran terbuka dan akun baru otomatis mendapat peran Petugas Gudang. Peran lain hanya bisa dibuat lewat menu Pengguna oleh administrator. Akun yang dinonaktifkan akan ditolak saat login.

## Persyaratan

- PHP 8.3 atau lebih baru
- Composer
- Node.js dan npm
- SQLite secara bawaan, atau MySQL/PostgreSQL lewat konfigurasi `DB_*`

## Instalasi

```bash
composer run setup
```

Perintah tersebut memasang dependensi, menyalin `.env.example` menjadi `.env` bila belum ada, membuat `APP_KEY`, menjalankan migrasi, memasang paket npm, dan membangun aset frontend.

Untuk menjalankan server pengembangan:

```bash
composer run dev
```

Atau jalankan bagian-bagiannya terpisah bila tidak ingin memakai `composer run dev`:

```bash
php artisan serve
npm run dev
```

## Akun Awal

`composer run setup` menjalankan migrasi, bukan seeder. Untuk mengisi data contoh, jalankan:

```bash
php artisan db:seed
```

Seeder membuat dua pengguna, empat kategori, tiga supplier, dua lokasi, dan delapan produk Vasco beserta stok awalnya.

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Administrator | `admin@inventori.test` | `password` |
| Petugas Gudang | `petugas@inventori.test` | `password` |

Kata sandi ini untuk pengembangan. Ganti sebelum dipakai di lingkungan lain.

## Memeriksa Stok Manual

Notifikasi biasanya terkirim otomatis saat ada transaksi. Untuk memeriksa ulang tanpa transaksi, jalankan perintah berikut:

```bash
php artisan stok:periksa
```

Perintah hanya mengirim pada produk yang belum diberi peringatan dalam 24 jam terakhir. Tambahkan `--force` untuk mengirim ulang ke seluruh produk yang stoknya menipis atau habis.

## Arsitektur

Query tidak ditulis di controller. Controller mengambil data lewat interface di `app/Contracts/Repositories`, implementasi Eloquent-nya ada di `app/Repositories/Eloquent`, dan ikatannya dikendalikan di `app/Providers/AppServiceProvider.php`.

```
app/
├── Contracts/Repositories/   interface per sumber data
├── Repositories/Eloquent/    implementasi query
├── Services/                 StockService, StockNotifier
├── Enums/                    RoleName, StockMoveType, StockStatus
├── Http/Controllers/         tipis, hanya mengatur alur
├── Http/Requests/            validasi input
└── Support/                  PageSize, TimeAgo
```

`StockService` adalah satu-satunya tempat yang mengubah stok. Setiap perubahan berjalan di dalam transaksi database dan mengunci baris produk serta baris stok lokasi dengan `lockForUpdate`, lalu menolak transaksi yang membuat stok lokasi menjadi negatif lewat `InsufficientStockException`. Transfer menghasilkan dua baris mutasi yang saling ditautkan lewat `related_move_id` dengan satu nomor referensi yang sama.

Status stok ditentukan di `StockStatus::of($stock, $minStock)`: nol berarti Habis, sama dengan atau di bawah stok minimum berarti Menipis, sisanya Aman.

## Model Data

| Tabel | Isi |
| --- | --- |
| `products` | Katalog produk beserta total stok dan stok minimum |
| `location_stocks` | Stok per pasangan produk dan lokasi |
| `stock_moves` | Riwayat setiap perubahan stok |
| `categories`, `suppliers`, `locations` | Master data pendukung |
| `roles`, `users` | Pengguna dan perannya |
| `notifications` | Notifikasi stok |

Total stok pada `products.stock` adalah jumlah seluruh lokasi, sedangkan `location_stocks` menyimpan rincian per lokasi. Keduanya diperbarui bersamaan dalam satu transaksi.

## Antarmuka

Frontend memakai CSS murni dengan penamaan BEM, bukan Tailwind. Ikon memakai Bootstrap Icons, konfirmasi hapus memakai SweetAlert2, dan bundling memakai Vite. Aturan lengkapnya, termasuk design token dan komponen wajib, ada di `DESIGN.md` pada salinan lokal proyek ini.

## Pengujian

```bash
php artisan test
```

## Lisensi

MIT, mengikuti deklarasi pada `composer.json`.
