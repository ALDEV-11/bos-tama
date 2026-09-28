<?php

namespace App\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\StockStatus;
use App\Models\Product;
use App\Notifications\LowStockNotification;
use App\Notifications\StockOutNotification;
use Illuminate\Support\Facades\Notification;

class StockNotifier
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly ProductRepositoryInterface $products,
    ) {}

    /**
     * Notifikasi dikirim saat status stok berubah (tidak spam di setiap transaksi)
     * atau saat pemeriksaan ulang dipaksa lewat artisan command.
     */
    public function evaluate(Product $product, int $stockBefore, bool $force = false): bool
    {
        $status = StockStatus::of($product->stock, $product->min_stock);

        if (! $this->shouldNotify($status, $stockBefore, $product, $force)) {
            return false;
        }

        $recipients = $this->users->admins();

        if ($recipients->isEmpty()) {
            return false;
        }

        $notification = $status === StockStatus::Out
            ? new StockOutNotification($product)
            : new LowStockNotification($product, $stockBefore);

        Notification::send($recipients, $notification);

        $this->products->markAlerted($product->id);

        return true;
    }

    private function shouldNotify(StockStatus $status, int $stockBefore, Product $product, bool $force): bool
    {
        if ($status === StockStatus::Safe) {
            return false;
        }

        if ($force) {
            return true;
        }

        return StockStatus::of($stockBefore, $product->min_stock) !== $status;
    }
}
