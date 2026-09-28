<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Product $product,
        public readonly int $stockBefore,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'level' => 'warning',
            'icon' => 'bi-exclamation-triangle',
            'title' => 'Stok Menipis: '.$this->product->name,
            'message' => "Stok {$this->product->name} tersisa {$this->product->stock} {$this->product->unit}, batas minimum {$this->product->min_stock}.",
            'product_id' => $this->product->id,
            'url' => route('produk.show', $this->product),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Peringatan Stok Menipis: '.$this->product->name)
            ->greeting('Halo '.$notifiable->name)
            ->line("Stok {$this->product->name} ({$this->product->sku}) turun dari {$this->stockBefore} menjadi {$this->product->stock} {$this->product->unit}.")
            ->line("Batas minimum yang ditetapkan adalah {$this->product->min_stock} {$this->product->unit}. Segera lakukan restok.")
            ->action('Lihat Produk', route('produk.show', $this->product))
            ->line('Terima kasih.');
    }
}
