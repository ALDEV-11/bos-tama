<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\StockMoveRepositoryInterface;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboard,
        private readonly ProductRepositoryInterface $products,
        private readonly StockMoveRepositoryInterface $moves,
    ) {}

    public function index(): View
    {
        return view('dashboard', [
            'summary' => $this->dashboard->summary(),
            'chart' => $this->dashboard->dailyMovement(),
            'lowStock' => $this->products->needsAttention(6),
            'recentMoves' => $this->moves->latest(5),
        ]);
    }
}
