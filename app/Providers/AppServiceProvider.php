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
            // Always prefer forcing scheme in production, but be defensive when
            // reading proxy headers (they may be arrays or not available in CLI).
            URL::forceScheme('https');

            // If the request indicates it arrived over HTTPS via a reverse proxy
            // (X-Forwarded-Proto), ensure we also force the scheme. Handle the
            // possibility that the header can be an array.
            $proto = null;

            // Prefer server superglobal when available (safer during CLI boots)
            if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'];
            } else {
                // Fallback: avoid using the `request()` helper so static analyzers
                // don't infer mixed/array types. Prefer the superglobal and then
                // try `getenv()` as a last resort.
                $proto = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? getenv('HTTP_X_FORWARDED_PROTO')) ?: null;
            }

            if ($proto && strtolower((string) $proto) === 'https') {
                URL::forceScheme('https');
            }
        }
    }
}
