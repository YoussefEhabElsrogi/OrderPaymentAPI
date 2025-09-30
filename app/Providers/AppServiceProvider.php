<?php

namespace App\Providers;

use App\Repositories\
{
    UserRepository,
    OrderRepository,
    PaymentRepository,
};
use App\Interfaces\Repositories\{
    UserRepositoryInterface,
    OrderRepositoryInterface,
    PaymentRepositoryInterface,
};
use App\Interfaces\Services\{
    UserServiceInterface,
    OrderServiceInterface,
    PaymentServiceInterface,
};
use App\Services\{
    UserService,
    OrderService,
    PaymentService,
};
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // User
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);

        // Order
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);

        // Payment
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PaymentServiceInterface::class, PaymentService::class);

        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
