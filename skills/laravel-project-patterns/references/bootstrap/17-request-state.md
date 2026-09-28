# Configuration: Session Cache and Inertia State

The blocks contain selected entries from `config/session.php`, `config/cache.php` and `config/inertia.php`, respectively. Merge them into the existing files. JSON session serialization, permitted cache classes and encrypted Inertia history are separate settings; preserve the value types and environment overrides.

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Str;

return [
    'cookie' => env('SESSION_COOKIE', Str::slug((string) env('APP_NAME', 'laravel')).'-session'),
    'serialization' => 'json',
];
```

```php
<?php

declare(strict_types=1);

return [
    'serializable_classes' => false,
];
```

```php
<?php

declare(strict_types=1);

return [
    'expose_shared_prop_keys' => true,
    'history' => [
        'encrypt' => (bool) env('INERTIA_ENCRYPT_HISTORY', true),
    ],
    'pages' => [
        'ensure_pages_exist' => true,
        'extensions' => ['js', 'jsx', 'svelte', 'ts', 'tsx', 'vue'],
        'paths' => [resource_path('js/pages')],
    ],
    'testing' => [
        'ensure_pages_exist' => true,
    ],
];
```
