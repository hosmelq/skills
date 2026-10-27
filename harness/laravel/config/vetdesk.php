<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Users
    |--------------------------------------------------------------------------
    |
    | Define a small allowlist of admin users without persisting the role in
    | the database. Provide a comma-separated list of email addresses using
    | the ADMIN_EMAILS environment variable.
    |
    */

    'admin' => [
        'emails' => [
            ...array_filter(
                explode(',', (string) env('ADMIN_EMAILS', '')),
                static fn (string $email): bool => $email !== '',
            ),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sqids
    |--------------------------------------------------------------------------
    |
    | Sqids are computed from internal integer identifiers before they are
    | exposed publicly. The package's default alphabet and blocklist remain
    | unchanged, while this value sets the minimum public identifier length
    | across every model that uses the HasSqid concern.
    |
    */

    'sqids' => [
        'min_length' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | World Database
    |--------------------------------------------------------------------------
    |
    | This option specifies the URL from which the world database SQLite file
    | will be downloaded. This database contains comprehensive geographical
    | data including regions, subregions, countries, states, and cities.
    |
    */

    'world_database' => [
        'download_url' => 'https://github.com/dr5hn/countries-states-cities-database/raw/9d8c3e1c37c15366285a279d1e43cec6c53d6a5b/sqlite/world.sqlite3.gz',
    ],

];
