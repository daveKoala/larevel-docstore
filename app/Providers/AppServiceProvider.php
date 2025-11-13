<?php

namespace App\Providers;

use App\Contracts\HealthServiceInterface;
use App\Contracts\OrderServiceInterface;
use App\Services\HealthService;
use App\Services\OrderService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register default service implementations
        $this->app->bind(HealthServiceInterface::class, HealthService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
