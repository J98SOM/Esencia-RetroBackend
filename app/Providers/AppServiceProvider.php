<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Force HTTPS in production (for Railway and other hosting with proxies)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');

            // Trust headers from reverse proxy (Railway, Heroku, etc.)
            if (request()->header('x-forwarded-proto') === 'https') {
                URL::forceScheme('https');
            }
        }
    }
}
