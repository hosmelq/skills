# Configuration: Parse Environment Values

The first block is `config/admin.php`; it splits the list and removes only empty strings, preserving whitespace and `"0"`. Merge the second block into `config/app.php` for previous keys. Keep `env()` access in configuration files.

```php
<?php

declare(strict_types=1);

return [
    'emails' => [
        ...array_filter(
            explode(',', (string) env('ADMIN_EMAILS', '')),
            static fn (string $email): bool => $email !== '',
        ),
    ],
];
```

```php
<?php

declare(strict_types=1);

return [
    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', '')),
            static fn (string $key): bool => $key !== '',
        ),
    ],
];
```
