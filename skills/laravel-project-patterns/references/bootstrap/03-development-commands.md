# Providers: Configure Development Commands

Keep production protection independent from development process registration. The launcher uses Portless environment variables for the HTTP and admin ports; replace the synthetic process name for the local setup.

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\DevCommands;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class DevelopmentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureCommands();
    }

    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());

        DevCommands::only('logs', 'queue', 'schedule', 'server', 'vite');

        DevCommands::artisan('schedule:work --whisper', 'schedule');

        DevCommands::register(
            <<<'COMMAND'
            nub exec portless run sh -c 'exec php artisan octane:start --admin-port="$((PORT + 10000))" --host="$HOST" --port="$PORT" --watch'
            COMMAND,
            'server',
        );
        DevCommands::register('nub exec portless run --name sample-vite nub run dev', 'vite');
    }
}
```
