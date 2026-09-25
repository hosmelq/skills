# Console Tests: Forced Download

With `--force`, a valid download replaces the sentinel contents; a malformed SQLite payload throws `PDOException` and retains them. Use gzip fixtures containing a small synthetic SQLite database and non-SQLite bytes, respectively, plus an existing writable test directory. The malformed archive must decompress successfully; only the initial-download example asserts SQLite integrity.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

it('downloads and replaces the database when forced', function (): void {
    Http::preventStrayRequests();

    $databasePath = storage_path('framework/testing/forced-catalog.sqlite3');

    Config::set('database.connections.catalog.database', $databasePath);

    File::put($databasePath, 'existing catalog database');

    Http::fake([
        Config::string('catalog-database.download_url') => Http::response(
            File::get(base_path('tests/testfiles/catalog.sqlite3.gz')),
        ),
    ]);

    artisan('app:download-catalog-database', ['--force' => true])->assertSuccessful();

    expect(File::get($databasePath))->not->toBe('existing catalog database');

    File::delete($databasePath);
});

it('preserves the existing database when a forced download is malformed', function (): void {
    Http::preventStrayRequests();

    $databasePath = storage_path('framework/testing/invalid-catalog.sqlite3');

    Config::set('database.connections.catalog.database', $databasePath);

    File::put($databasePath, 'existing catalog database');

    Http::fake([
        Config::string('catalog-database.download_url') => Http::response(
            File::get(base_path('tests/testfiles/malformed-catalog.sqlite3.gz')),
        ),
    ]);

    expect(fn () => artisan('app:download-catalog-database', ['--force' => true]))
        ->toThrow(PDOException::class);

    $databaseContents = File::get($databasePath);

    expect($databaseContents)->toBe('existing catalog database');

    File::delete($databasePath);
});
```
