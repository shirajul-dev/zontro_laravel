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
        RateLimiter::for('piprapay_api', function (Request $request) {
            $limit = (int) config('piprapay.security.api_rate_limit_per_minute', 120);
            if ($limit <= 0) {
                $limit = 120;
            }

            $apiKey = (string) $request->header('MHS-PIPRAPAY-API-KEY', '');
            $identifier = $apiKey !== '' ? 'key:' . hash('sha256', $apiKey) : 'ip:' . (string) $request->ip();

            return Limit::perMinute($limit)->by($identifier);
        });
    }
}
