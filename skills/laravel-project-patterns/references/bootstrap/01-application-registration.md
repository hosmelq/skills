# Bootstrap: Register the Application

Configure route files, middleware aliases and exception reporting in `bootstrap/app.php`. Keep web and API middleware distinct. Register application providers in `bootstrap/providers.php`; when adapting focused provider examples, merge their methods into the registered provider or register the new class.

```php
<?php

declare(strict_types=1);

use App\Http\Middleware\DecodeSqids;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware
            ->alias([
                'admin' => EnsureAdmin::class,
                'sqids' => DecodeSqids::class,
            ])
            ->redirectGuestsTo(fn (): string => route('login'))
            ->redirectUsersTo('/')
            ->throttleApi()
            ->web(append: [
                HandleInertiaRequests::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })->create();
```

```php
<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\NanoIDServiceProvider;
use App\Providers\SqidsServiceProvider;
use App\Providers\TypeScriptTransformerServiceProvider;

return [
    AdminPanelServiceProvider::class,
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    NanoIDServiceProvider::class,
    SqidsServiceProvider::class,
    TypeScriptTransformerServiceProvider::class,
];
```

Provider implementations: [NanoID](08-deferred-binding.md), [Sqids](../support/03-sqid-codec.md), [enum types](09-enum-types.md), [admin panel](10-admin-panel.md).
