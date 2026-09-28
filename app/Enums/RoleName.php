<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'admin';
    case Petugas = 'petugas';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Petugas => 'Petugas Gudang',
        };
    }
}
