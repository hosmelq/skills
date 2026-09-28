# Configuration: Authentication Options

In `config/fortify.php`, select features separately from registered provider callbacks: this example enables registration and email verification. Listing passkey options or a limiter name does not enable that feature. The second block contains selected `config/services.php` entries; keep other service keys.

```php
<?php

declare(strict_types=1);

use function Safe\parse_url;

use Laravel\Fortify\Features;

return [
    'domain' => null,
    'email' => 'email',
    'features' => [
        Features::emailVerification(),
        Features::registration(),
    ],
    'guard' => 'web',
    'home' => '/',
    'limiters' => [
        'login' => 'login',
        'passkeys' => 'passkeys',
        'two-factor' => 'two-factor',
    ],
    'lowercase_usernames' => true,
    'middleware' => ['web'],
    'passkeys' => [
        'allowed_origins' => [config('app.url')],
        'relying_party_id' => parse_url((string) config('app.url'), PHP_URL_HOST),
        'timeout' => 60000,
    ],
    'passwords' => 'users',
    'prefix' => '',
    'username' => 'email',
    'views' => true,
];
```

```php
<?php

declare(strict_types=1);

return [
    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
    ],
    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],
    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],
];
```
