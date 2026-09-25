# Console Tests: Database Download

A command downloads and extracts a missing SQLite database; assert a successful exit and `PRAGMA integrity_check` returning `ok`. Use a gzip fixture containing a small synthetic SQLite database at the illustrated fixture path, an existing writable test directory and the configured download URL.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

it('downloads and extracts the database', function (): void {
    Http::preventStrayRequests();

    $databasePath = storage_path('framework/testing/catalog.sqlite3');

    Config::set('database.connections.catalog.database', $databasePath);

    File::delete($databasePath);

    Http::fake([
        Config::string('catalog-database.download_url') => Http::response(
            File::get(base_path('tests/testfiles/catalog.sqlite3.gz')),
        ),
    ]);

    artisan('app:download-catalog-database')->assertSuccessful();

    $database = new PDO('sqlite:'.$databasePath);

    expect($database->query('PRAGMA integrity_check')->fetchColumn())->toBe('ok');

    File::delete($databasePath);
});
```
