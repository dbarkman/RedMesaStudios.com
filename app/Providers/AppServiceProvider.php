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
            $ip = $request->ip();
            $deniedIp = DeniedIp::where('ip_address', $ip)->first();

            if ($deniedIp && $deniedIp->banned_until && $deniedIp->banned_until->isFuture()) {
                return Limit::none()->response(fn () => response('Your IP is temporarily banned.', 429));
            }

            $key = 'rate-limiter:' . $ip;
            $limit = Limit::perMinute(60)->by($ip);

            if (RateLimiter::tooManyAttempts($key, 60)) {
                $offenseCount = optional($deniedIp)->offense_count ?? 0;
                $offenseCount++;

                $banDuration = match ($offenseCount) {
                    1 => now()->addHour(),
                    2 => now()->addHours(6),
                    3 => now()->addHours(12),
                    default => now()->addYears(100),
                };

                DeniedIp::updateOrCreate(
                    ['ip_address' => $ip],
                    ['offense_count' => $offenseCount, 'banned_until' => $banDuration]
                );
            }

            return $limit;
        });
    }
}

