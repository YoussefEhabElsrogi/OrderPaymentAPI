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

        // Order
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);

        // Payment
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);

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
