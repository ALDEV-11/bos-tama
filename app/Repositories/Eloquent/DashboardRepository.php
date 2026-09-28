<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Enums\StockMoveType;
use App\Models\Product;
use App\Models\StockMove;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function summary(): array
    {
        return [
            'total_products' => Product::query()->where('is_active', true)->count(),
            'total_stock' => (int) Product::query()->sum('stock'),
            'stock_value' => (float) Product::query()->sum(DB::raw('stock * price')),
            'low_stock' => Product::query()->active()->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock')->count(),
            'out_stock' => Product::query()->active()->where('stock', 0)->count(),
        ];
    }

    public function dailyMovement(int $days = 7): array
    {
        $start = Carbon::today()->subDays($days - 1);

        $rows = $this->movementQuery(
            [
                StockMoveType::In->value,
                StockMoveType::ReturnIn->value,
                StockMoveType::Out->value,
                StockMoveType::ReturnOut->value,
            ],
            $start,
        )
            ->selectRaw('DATE(created_at) as tanggal, type, SUM(qty) as total')
            ->groupBy('tanggal', 'type')
            ->get();

        $series = [];

        for ($day = 0; $day < $days; $day++) {
            $date = $start->copy()->addDays($day);
            $series[$date->toDateString()] = ['label' => $date->translatedFormat('D'), 'in' => 0, 'out' => 0];
        }

        foreach ($rows as $row) {
            $key = Carbon::parse($row->tanggal)->toDateString();

            if (! isset($series[$key])) {
                continue;
            }

            $type = $row->type instanceof StockMoveType ? $row->type : StockMoveType::from($row->type);

            $series[$key][$type->increasesStock() ? 'in' : 'out'] = (int) $row->total;
        }

        return $series;
    }

    /**
     * @param  array<int, string>  $types
     * @return Builder<StockMove>
     */
    private function movementQuery(array $types, Carbon $from): Builder
    {
        return StockMove::query()
            ->whereIn('type', $types)
            ->where('created_at', '>=', $from->startOfDay());
    }
}
