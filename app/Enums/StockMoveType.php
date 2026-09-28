<?php

namespace App\Enums;

enum StockMoveType: string
{
    case In = 'masuk';
    case Out = 'keluar';
    case TransferOut = 'transfer_keluar';
    case TransferIn = 'transfer_masuk';
    case ReturnIn = 'retur_masuk';
    case ReturnOut = 'retur_keluar';
    case Adjustment = 'penyesuaian';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Stok Masuk',
            self::Out => 'Stok Keluar',
            self::TransferOut => 'Transfer Keluar',
            self::TransferIn => 'Transfer Masuk',
            self::ReturnIn => 'Retur Masuk',
            self::ReturnOut => 'Retur Keluar',
            self::Adjustment => 'Penyesuaian Stok',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::In, self::ReturnIn, self::TransferIn => 'success',
            self::Out, self::ReturnOut, self::TransferOut => 'info',
            self::Adjustment => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::In => 'bi-arrow-down-circle',
            self::Out => 'bi-arrow-up-circle',
            self::TransferOut, self::TransferIn => 'bi-arrow-left-right',
            self::ReturnIn => 'bi-arrow-counterclockwise',
            self::ReturnOut => 'bi-arrow-return-left',
            self::Adjustment => 'bi-sliders',
        };
    }

    public function increasesStock(): bool
    {
        return match ($this) {
            self::In, self::ReturnIn, self::TransferIn => true,
            default => false,
        };
    }
}
