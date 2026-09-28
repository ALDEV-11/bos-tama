<?php

namespace App\Providers;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Contracts\Repositories\LocationRepositoryInterface;
use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\StockMoveRepositoryInterface;
use App\Contracts\Repositories\SupplierRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\DashboardRepository;
use App\Repositories\Eloquent\LocationRepository;
use App\Repositories\Eloquent\NotificationRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\StockMoveRepository;
use App\Repositories\Eloquent\SupplierRepository;
use App\Repositories\Eloquent\UserRepository;
use App\View\Composers\SidebarComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Repository Pattern: setiap kontrak dipetakan ke implementasi Eloquent-nya.
     */
    public function register(): void
    {
        $this->app->singleton(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->singleton(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->singleton(SupplierRepositoryInterface::class, SupplierRepository::class);
        $this->app->singleton(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->singleton(StockMoveRepositoryInterface::class, StockMoveRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->singleton(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->singleton(RoleRepositoryInterface::class, RoleRepository::class);
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination::inventori');

        Blade::directive('timeago', fn (string $expression) => "<?php echo e(\\App\\Support\\TimeAgo::human({$expression})); ?>");

        View::composer('components.layouts.app', SidebarComposer::class);
    }
}
