<?php

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'contact_name', 'phone', 'email', 'address', 'is_active'])]
class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @param Builder<Supplier> $query */
    public function scopeSearch(Builder $query, ?string $keyword): void
    {
        $query->when($keyword, fn (Builder $q) => $q->where(function (Builder $inner) use ($keyword) {
            $inner->where('name', 'like', "%{$keyword}%")
                ->orWhere('contact_name', 'like', "%{$keyword}%");
        }));
    }

    /** @param Builder<Supplier> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
