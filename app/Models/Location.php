<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description', 'is_active'])]
class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    public function locationStocks(): HasMany
    {
        return $this->hasMany(LocationStock::class);
    }

    /** @param Builder<Location> $query */
    public function scopeSearch(Builder $query, ?string $keyword): void
    {
        $query->when($keyword, fn (Builder $q) => $q->where(function (Builder $inner) use ($keyword) {
            $inner->where('name', 'like', "%{$keyword}%")
                ->orWhere('code', 'like', "%{$keyword}%");
        }));
    }

    /** @param Builder<Location> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
