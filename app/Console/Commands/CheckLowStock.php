<?php

namespace App\Console\Commands;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Enums\StockStatus;
use App\Models\Product;
use App\Services\StockNotifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('stok:periksa {--force : Kirim ulang meski belum ada status baru}')]
#[Description('Periksa produk yang stoknya menipis atau habis lalu kirim notifikasi ke administrator')]
class CheckLowStock extends Command
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly StockNotifier $notifier,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $candidates = $this->products
            ->allActive()
            ->filter(fn (Product $product) => $product->status() !== StockStatus::Safe)
            ->reject(fn (Product $product) => $this->wasRecentlyAlerted($product, $force));

        if ($candidates->isEmpty()) {
            $this->components->info('Tidak ada produk yang perlu diberi peringatan.');

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($candidates as $product) {
            $sent += (int) $this->notifier->evaluate($product, $product->stock, force: true);
        }

        $this->components->info("{$sent} notifikasi dikirim dari ".count($candidates).' kandidat.');

        return self::SUCCESS;
    }

    private function wasRecentlyAlerted(Product $product, bool $force): bool
    {
        if ($force || $product->last_alerted_at === null) {
            return false;
        }

        return $product->last_alerted_at->gt(now()->subDay());
    }
}
