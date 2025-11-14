<?php

namespace App\Providers;

use App\Contracts\HealthServiceInterface;
use App\Contracts\NotificationServiceInterface;
use App\Contracts\OrderServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Services\HealthService;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\UserService;
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
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
