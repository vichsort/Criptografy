<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The API is public: without a limit, any client could exhaust the instance.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(
            config()->integer('app.api_rate_limit'),
        )->by((string) $request->ip()));
    }
}
