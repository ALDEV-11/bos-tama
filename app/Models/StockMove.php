<?php

namespace App\Models;

use App\Enums\StockMoveType;
use Database\Factories\StockMoveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'type',
    'qty',
    'stock_before',
    'stock_after',
    'from_location_id',
    'to_location_id',
    'reference',
    'note',
    'user_id',
    'related_move_id',
])]
class StockMove extends Model
{
    /** @use HasFactory<StockMoveFactory> */
    use HasFactory;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function relatedMove(): BelongsTo
    {
        return $this->belongsTo(self::class, 'related_move_id');
    }

    public function typeLabel(): string
    {
        return $this->type->label();
    }

    /** @param Builder<StockMove> $query */
    public function scopeInbound(Builder $query): void
    {
        $query->whereIn('type', [
            StockMoveType::In->value,
            StockMoveType::ReturnIn->value,
            StockMoveType::TransferIn->value,
        ]);
    }

    /** @param Builder<StockMove> $query */
    public function scopeOutbound(Builder $query): void
    {
        $query->whereIn('type', [
            StockMoveType::Out->value,
            StockMoveType::ReturnOut->value,
            StockMoveType::TransferOut->value,
        ]);
    }

    protected function casts(): array
    {
        return [
            'type' => StockMoveType::class,
            'qty' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
        ];
    }
}
