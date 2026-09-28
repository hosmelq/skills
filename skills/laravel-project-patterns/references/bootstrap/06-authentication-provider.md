# Providers: Configure Authentication

Register Fortify action callbacks and Inertia views. Login limiting lowercases the configured username and combines it with the IP; two-factor limiting uses `login.id` from the session.

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(
            RedirectIfTwoFactorAuthenticatable::class
        );
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);

        Fortify::loginView(function () {
            return Inertia::render('auth/Login');
        });

        Fortify::verifyEmailView(function () {
            return Inertia::render('auth/VerifyEmail');
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(sprintf(
                '%s|%s',
                Str::lower((string) $request->string(Fortify::username())),
                $request->ip()
            ));

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
```

Use the matching [authentication configuration](07-authentication-options.md).
