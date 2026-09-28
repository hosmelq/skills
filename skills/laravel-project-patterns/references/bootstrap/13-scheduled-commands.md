# Routes: Schedule Commands

Declare schedules in `routes/console.php`. The `disposable:update` command requires `propaganistas/laravel-disposable-email`. Keep the health-check and heartbeat commands together; queue and scheduler heartbeats measure different workers.

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('disposable:update')->weekly();

Schedule::command('health:check')->everyMinute();
Schedule::command('health:queue-check-heartbeat')->everyMinute();
Schedule::command('health:schedule-check-heartbeat')->everyMinute();

Schedule::command('model:prune')->daily();
```
