# Configuration: Queue Storage and Timing

Merge these entries into `config/queue.php`, keeping table names aligned with migrations. `retry_after` controls when a reserved job becomes available again; it is separate from job retries and backoff. Preserve `after_commit` independently and keep the worker timeout shorter than `retry_after`.

```php
<?php

declare(strict_types=1);

return [
    'batching' => [
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'queue_job_batches',
    ],
    'connections' => [
        'background' => [
            'driver' => 'background',
        ],
        'database' => [
            'after_commit' => false,
            'connection' => env('DB_QUEUE_CONNECTION'),
            'driver' => 'database',
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 210),
            'table' => env('DB_QUEUE_TABLE', 'queue_jobs'),
        ],
        'deferred' => [
            'driver' => 'deferred',
        ],
        'failover' => [
            'connections' => ['database', 'deferred'],
            'driver' => 'failover',
        ],
        'redis' => [
            'after_commit' => false,
            'block_for' => null,
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'driver' => 'redis',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 210),
        ],
    ],
    'default' => env('QUEUE_CONNECTION', 'database'),
    'failed' => [
        'database' => env('DB_CONNECTION', 'sqlite'),
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'table' => 'queue_failed_jobs',
    ],
];
```
