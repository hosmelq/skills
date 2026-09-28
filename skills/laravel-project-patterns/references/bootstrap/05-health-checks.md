# Providers: Configure Health Checks

Register checks and schedule their commands together. The cache result store uses the database cache store. Merge the selected `config/health.php` keys into its existing configuration; notification throttling and the `only_on_failure` choice are independent.

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\QueueCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Facades\Health;

class HealthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureLaravelHealth();
    }

    private function configureLaravelHealth(): void
    {
        Health::checks([
            CacheCheck::new(),
            DatabaseCheck::new(),
            DebugModeCheck::new(),
            OptimizedAppCheck::new(),
            QueueCheck::new()->failWhenHealthJobTakesLongerThanMinutes(2),
            ScheduleCheck::new()->heartbeatMaxAgeInMinutes(2),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

use Spatie\Health\Notifications\CheckFailedNotification;
use Spatie\Health\ResultStores\CacheHealthResultStore;

return [
    'json_results_failure_status' => 200,
    'notifications' => [
        'enabled' => true,
        'notifications' => [
            CheckFailedNotification::class => ['slack'],
        ],
        'only_on_failure' => false,
        'slack' => [
            'channel' => null,
            'icon' => null,
            'username' => null,
            'webhook_url' => env('HEALTH_SLACK_WEBHOOK_URL', ''),
        ],
        'throttle_notifications_for_minutes' => 60,
    ],
    'result_stores' => [
        CacheHealthResultStore::class => [
            'store' => 'database',
        ],
    ],
    'silence_health_queue_job' => true,
];
```

Run the [scheduled commands](13-scheduled-commands.md); [web routes](11-web-routes.md) separate public health from protected results.
