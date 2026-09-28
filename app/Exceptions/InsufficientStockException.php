<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly string $productName,
        public readonly string $locationName,
        public readonly int $requested,
        public readonly int $available,
    ) {
        parent::__construct("Stok {$productName} di {$locationName} tidak cukup. Tersedia {$available}, diminta {$requested}.");
    }

    public function messageForUser(): string
    {
        return "Stok {$this->productName} di lokasi {$this->locationName} tidak mencukupi. Tersedia {$this->available}, diminta {$this->requested}.";
    }
}
