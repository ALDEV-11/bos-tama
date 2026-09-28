<?php

namespace App\Models;

use App\Enums\StockStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sku', 'name', 'description', 'category_id', 'supplier_id', 'unit', 'price', 'min_stock', 'stock', 'is_active', 'last_alerted_at'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function locationStocks(): HasMany
    {
        return $this->hasMany(LocationStock::class);
    }

    public function stockMoves(): HasMany
    {
        return $this->hasMany(StockMove::class);
    }

    public function status(): StockStatus
    {
        return StockStatus::of($this->stock, $this->min_stock);
    }

    /** Rasio stok terhadap batas minimum, dipakai untuk progress bar. */
    public function stockPercentage(): int
    {
        if ($this->min_stock <= 0) {
            return 100;
        }

        return (int) min(100, round($this->stock / $this->min_stock * 100));
    }

    public function stockValue(): float
    {
        return (float) $this->price * $this->stock;
    }

    /** @param Builder<Product> $query */
    public function scopeSearch(Builder $query, ?string $keyword): void
    {
        $query->when($keyword, fn (Builder $q) => $q->where(function (Builder $inner) use ($keyword) {
            $inner->where('name', 'like', "%{$keyword}%")
                ->orWhere('sku', 'like', "%{$keyword}%");
        }));
    }

    /** @param Builder<Product> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
            'last_alerted_at' => 'datetime',
        ];
    }
}
