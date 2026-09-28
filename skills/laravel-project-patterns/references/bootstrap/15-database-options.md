# Configuration: Database Connections

Merge these selected entries into `config/database.php`. The secondary SQLite connection uses its own URL and file; MySQL and MariaDB remove only null, false and empty SSL options, preserving zero. The second block is `config/catalog-database.php` for the download command; use an immutable, version-pinned dataset URL when adapting.

```php
<?php

declare(strict_types=1);

use Pdo\Mysql;

return [
    'connections' => [
        'mariadb' => [
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ], static fn (mixed $value): bool => ! in_array($value, [null, false, ''], true)) : [],
        ],
        'mysql' => [
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ], static fn (mixed $value): bool => ! in_array($value, [null, false, ''], true)) : [],
        ],
        'pgsql' => [
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],
        'catalog' => [
            'busy_timeout' => null,
            'database' => env('DB_DATABASE_CATALOG', database_path('catalog.sqlite3')),
            'driver' => 'sqlite',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'journal_mode' => null,
            'prefix' => '',
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
            'url' => env('DB_URL_CATALOG'),
        ],
    ],
];
```

```php
<?php

declare(strict_types=1);

return [
    'download_url' => 'https://datasets.example.test/v1/catalog.sqlite3.gz',
];
```

The [download command](../console-notifications/01-database-download.md) consumes both configuration keys.
