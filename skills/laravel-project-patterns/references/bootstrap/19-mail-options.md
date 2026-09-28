# Configuration: Mail Transports

Merge these entries into `config/mail.php`. SMTP derives its EHLO hostname with `Safe\parse_url`; the sender name falls back to the application name. Keep failover transport order; its retry interval is unrelated to queue job reservation timing.

```php
<?php

declare(strict_types=1);

use function Safe\parse_url;

return [
    'default' => env('MAIL_MAILER', 'log'),
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],
    'mailers' => [
        'failover' => [
            'mailers' => ['smtp', 'log'],
            'retry_after' => 60,
            'transport' => 'failover',
        ],
        'roundrobin' => [
            'mailers' => ['ses', 'postmark'],
            'retry_after' => 60,
            'transport' => 'roundrobin',
        ],
        'smtp' => [
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)
            ),
            'password' => env('MAIL_PASSWORD'),
            'port' => env('MAIL_PORT', 2525),
            'scheme' => env('MAIL_SCHEME'),
            'timeout' => null,
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'username' => env('MAIL_USERNAME'),
        ],
    ],
];
```
