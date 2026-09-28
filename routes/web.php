<?php

use App\Enums\RoleName;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockReturnController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.store');
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('notifikasi', [NotificationController::class, 'index'])->name('notifikasi.index');
    Route::patch('notifikasi/{notification}/baca', [NotificationController::class, 'read'])->name('notifikasi.read');
    Route::patch('notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifikasi.readAll');
    Route::delete('notifikasi', [NotificationController::class, 'clear'])->name('notifikasi.clear');

    Route::get('produk', [ProductController::class, 'index'])->name('produk.index');
    Route::get('produk/{product}', [ProductController::class, 'show'])->name('produk.show')->whereNumber('product');

    Route::post('stok-masuk', [StockInController::class, 'store'])->name('stok-masuk.store');
    Route::get('stok-masuk', [StockInController::class, 'index'])->name('stok-masuk.index');
    Route::get('stok-masuk/tambah', [StockInController::class, 'create'])->name('stok-masuk.create');

    Route::post('stok-keluar', [StockOutController::class, 'store'])->name('stok-keluar.store');
    Route::get('stok-keluar', [StockOutController::class, 'index'])->name('stok-keluar.index');
    Route::get('stok-keluar/tambah', [StockOutController::class, 'create'])->name('stok-keluar.create');

    Route::post('transfer', [StockTransferController::class, 'store'])->name('transfer.store');
    Route::get('transfer', [StockTransferController::class, 'index'])->name('transfer.index');
    Route::get('transfer/tambah', [StockTransferController::class, 'create'])->name('transfer.create');

    Route::post('retur', [StockReturnController::class, 'store'])->name('retur.store');
    Route::get('retur', [StockReturnController::class, 'index'])->name('retur.index');
    Route::get('retur/tambah', [StockReturnController::class, 'create'])->name('retur.create');

    Route::get('laporan', [ReportController::class, 'index'])->name('laporan.index');
    Route::get('laporan/cetak', [ReportController::class, 'printMutasi'])->name('laporan.cetak');
    Route::get('laporan/unduh', [ReportController::class, 'exportMutasi'])->name('laporan.unduh');
    Route::get('persediaan', [ReportController::class, 'persediaan'])->name('persediaan.index');
    Route::get('persediaan/unduh', [ReportController::class, 'exportPersediaan'])->name('persediaan.unduh');

    Route::middleware('role:'.RoleName::Admin->value)->group(function () {
        Route::get('produk/tambah', [ProductController::class, 'create'])->name('produk.create');
        Route::post('produk', [ProductController::class, 'store'])->name('produk.store');
        Route::get('produk/{product}/edit', [ProductController::class, 'edit'])->name('produk.edit')->whereNumber('product');
        Route::put('produk/{product}', [ProductController::class, 'update'])->name('produk.update');
        Route::delete('produk/{product}', [ProductController::class, 'destroy'])->name('produk.destroy');

        Route::get('kategori', [CategoryController::class, 'index'])->name('kategori.index');
        Route::post('kategori', [CategoryController::class, 'store'])->name('kategori.store');
        Route::put('kategori/{category}', [CategoryController::class, 'update'])->name('kategori.update');
        Route::delete('kategori/{category}', [CategoryController::class, 'destroy'])->name('kategori.destroy');

        Route::get('supplier', [SupplierController::class, 'index'])->name('supplier.index');
        Route::post('supplier', [SupplierController::class, 'store'])->name('supplier.store');
        Route::put('supplier/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
        Route::delete('supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');

        Route::get('lokasi', [LocationController::class, 'index'])->name('lokasi.index');
        Route::post('lokasi', [LocationController::class, 'store'])->name('lokasi.store');
        Route::put('lokasi/{location}', [LocationController::class, 'update'])->name('lokasi.update');
        Route::delete('lokasi/{location}', [LocationController::class, 'destroy'])->name('lokasi.destroy');

        Route::get('pengguna', [UserController::class, 'index'])->name('pengguna.index');
        Route::post('pengguna', [UserController::class, 'store'])->name('pengguna.store');
        Route::put('pengguna/{user}', [UserController::class, 'update'])->name('pengguna.update');
        Route::delete('pengguna/{user}', [UserController::class, 'destroy'])->name('pengguna.destroy');

        Route::post('opname', [StockAdjustmentController::class, 'store'])->name('opname.store');
        Route::get('opname', [StockAdjustmentController::class, 'index'])->name('opname.index');
        Route::get('opname/tambah', [StockAdjustmentController::class, 'create'])->name('opname.create');
    });
});
