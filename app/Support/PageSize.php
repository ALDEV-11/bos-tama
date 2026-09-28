<?php

namespace App\Support;

/**
 * Ukuran halaman tabel yang dipakai bersama agar pagination konsisten antar modul.
 */
final class PageSize
{
    /** Tabel transaksi dan laporan: 5 baris per halaman. */
    public const TRANSACTIONS = 5;

    /** Tabel master data: 15 baris per halaman. */
    public const MASTER_DATA = 15;

    /** Dipakai saat hasil perlu dimuat sekaligus, misalnya impor option select. */
    public const BULK = 500;
}
