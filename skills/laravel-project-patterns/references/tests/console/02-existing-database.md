# Console Tests: Existing Database

An existing target without `--force` produces the expected message, exits successfully and retains its contents. Stray HTTP requests remain blocked. Use an existing writable test directory and the inspected command/output contract.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

it('skips downloading when the database already exists', function (): void {
    Http::preventStrayRequests();

    $databasePath = storage_path('framework/testing/existing-catalog.sqlite3');

    Config::set('database.connections.catalog.database', $databasePath);

    File::put($databasePath, 'existing catalog database');

    artisan('app:download-catalog-database')
        ->expectsOutputToContain('Catalog SQLite database already exists.')
        ->assertSuccessful();

    expect(File::get($databasePath))->toBe('existing catalog database');

    File::delete($databasePath);
});
```
