<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;


use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\DeniedIp;

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
        RateLimiter::for('api', function (Request $request) {
            // Standard Laravel throttling: 60 req/min by client IP
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}

