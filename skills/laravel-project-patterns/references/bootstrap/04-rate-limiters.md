# Providers: Define Named Rate Limits

Use the same limiter names on routes. General API requests key by authenticated user ID or IP; provider login uses IP; email endpoints key by email plus IP without lowercasing here.

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiters();
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('api', function (Request $request): Limit {
            return Limit::perMinute(1000)->by($request->user()->id ?? $request->ip());
        });

        RateLimiter::for('apple.login', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('email.login', function (Request $request): Limit {
            return Limit::perMinute(10)->by(
                Str::transliterate(sprintf('%s|%s', $request->string('email'), $request->ip()))
            );
        });

        RateLimiter::for('email.request', function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                Str::transliterate(sprintf('%s|%s', $request->string('email'), $request->ip()))
            );
        });

        RateLimiter::for('google.login', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
```

See [API routes](12-api-routes.md) and the separate [Fortify login limiters](06-authentication-provider.md).
