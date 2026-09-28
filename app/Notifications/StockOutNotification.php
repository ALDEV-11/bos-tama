<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockOutNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Product $product) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'level' => 'danger',
            'icon' => 'bi-x-octagon',
            'title' => 'Stok Habis: '.$this->product->name,
            'message' => "Stok {$this->product->name} habis. Penjualan atau pengeluaran tidak dapat dilakukan sebelum restok.",
            'product_id' => $this->product->id,
            'url' => route('produk.show', $this->product),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Stok Habis: '.$this->product->name)
            ->greeting('Halo '.$notifiable->name)
            ->line("Stok {$this->product->name} ({$this->product->sku}) sudah habis dan perlu segera di-restock.")
            ->action('Lihat Produk', route('produk.show', $this->product))
            ->line('Terima kasih.');
    }
}
