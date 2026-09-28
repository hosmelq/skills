# Configuration: Development Tools and Workers

Merge these selected blocks into `config/boost.php` and `config/octane.php`, respectively. Keep the Octane lifecycle listener lists and worker cleanup from the installed package; these entries select its server, HTTPS behavior and watched paths.

```php
<?php

declare(strict_types=1);

return [
    'browser_log_levels' => explode(
        ',',
        (string) env('BOOST_BROWSER_LOG_LEVELS', 'error,warning,info,debug')
    ),
    'enforce_tests' => true,
    'guidelines' => [
        'exclude' => ['herd'],
    ],
    'rules' => [
        'scoped_guidelines' => env('BOOST_RULES_SCOPED_GUIDELINES', false),
    ],
];
```

```php
<?php

declare(strict_types=1);

return [
    'https' => env('OCTANE_HTTPS', true),
    'server' => env('OCTANE_SERVER', 'frankenphp'),
    'state_file' => env('OCTANE_STATE_FILE', storage_path('logs/octane-server-state.json')),
    'watch' => [
        'app',
        'bootstrap',
        'config/**/*.php',
        'database/**/*.php',
        'public/**/*.php',
        'resources/**/*.php',
        'routes',
        'composer.lock',
        '.env',
    ],
];
```
