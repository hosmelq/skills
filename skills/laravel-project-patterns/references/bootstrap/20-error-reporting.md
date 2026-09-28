# Configuration: Error Reporting and Logs

The first block contains selected `config/sentry.php` entries; the second contains `config/logging.php` channels. Merge them into the existing files. Keep event, trace and profile sample rates separate. Nullable integer options must remain null when absent; choose telemetry payload settings for the application.

```php
<?php

declare(strict_types=1);

return [
    'breadcrumbs' => [
        'sql_bindings' => env('SENTRY_BREADCRUMBS_SQL_BINDINGS_ENABLED', true),
    ],
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),
    'enable_logs' => env('SENTRY_ENABLE_LOGS', true),
    'enable_metrics' => env('SENTRY_ENABLE_METRICS', true),
    'ignore_transactions' => ['/up'],
    'log_flush_threshold' => env('SENTRY_LOG_FLUSH_THRESHOLD') === null
        ? null
        : (int) env('SENTRY_LOG_FLUSH_THRESHOLD'),
    'org_id' => env('SENTRY_ORG_ID') === null ? null : (int) env('SENTRY_ORG_ID'),
    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.25),
    'sample_rate' => (float) env('SENTRY_SAMPLE_RATE', 0.25),
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', true),
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.25),
    'tracing' => [
        'redis_commands' => env('SENTRY_TRACE_REDIS_COMMANDS', true),
        'sql_bindings' => env('SENTRY_TRACE_SQL_BINDINGS_ENABLED', true),
    ],
];
```

```php
<?php

declare(strict_types=1);

return [
    'channels' => [
        'monthly' => [
            'driver' => 'monthly',
            'level' => env('LOG_LEVEL', 'debug'),
            'max_files' => 3,
            'path' => storage_path('logs/laravel.log'),
            'replace_placeholders' => true,
        ],
        'sentry_logs' => [
            'driver' => 'sentry_logs',
            'level' => env('LOG_LEVEL', 'info'),
        ],
        'slack' => [
            'driver' => 'slack',
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', env('APP_NAME', 'Laravel')),
        ],
    ],
];
```
