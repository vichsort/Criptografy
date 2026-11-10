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
        // A API é pública: sem limite, qualquer cliente esgota a instância.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(
            (int) config('app.api_rate_limit'),
        )->by($request->ip()));
    }
}
