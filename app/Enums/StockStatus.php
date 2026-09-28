<?php

namespace App\Enums;

enum StockStatus: string
{
    case Safe = 'aman';
    case Low = 'menipis';
    case Out = 'habis';

    public static function of(int $stock, int $minStock): self
    {
        if ($stock === 0) {
            return self::Out;
        }

        return $stock <= $minStock ? self::Low : self::Safe;
    }

    public function label(): string
    {
        return match ($this) {
            self::Safe => 'Stok Aman',
            self::Low => 'Stok Menipis',
            self::Out => 'Stok Habis',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Safe => 'success',
            self::Low => 'warning',
            self::Out => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Safe => 'bi-check-circle',
            self::Low => 'bi-exclamation-triangle',
            self::Out => 'bi-x-octagon',
        };
    }
}
