# Console: Download Command

Download a generated asset the application needs, such as a lookup SQLite database, with an idempotent Artisan command that skips existing files unless forced, verifies the download and replaces the file atomically.

- Declare the signature and description with the `#[Signature]` and `#[Description]` attributes.
- Download into a temporary directory next to the target and `rename()` only after validating it, so a failed download keeps the current file.
- Keep the URL in the application's config file.
- Test with `Http::fake()`, fixture files in `tests/testfiles` and a database path under `storage/framework/testing`.

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use function Safe\copy;
use function Safe\rename;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use PDO;
use RuntimeException;
use Spatie\TemporaryDirectory\TemporaryDirectory;

#[Description('Download the World SQLite database.')]
#[Signature(
    'app:download-world-database {--force : Download the database even if it already exists}',
)]
final class DownloadWorldDatabaseCommand extends Command
{
    public function handle(): int
    {
        $databasePath = config()->string('database.connections.world.database');

        if (File::exists($databasePath) && ! $this->option('force')) {
            $this->components->info('World SQLite database already exists.');

            return Command::SUCCESS;
        }

        $tmp = new TemporaryDirectory(dirname($databasePath))->deleteWhenDestroyed()->create();
        $temporaryDatabasePath = $tmp->path('world.sqlite3');

        Http::retry(3, 500)
            ->sink($tmp->path('world.sqlite3.gz'))
            ->get(config()->string('vetdesk.world_database.download_url'))
            ->throw();

        copy(
            sprintf('compress.zlib://%s', $tmp->path('world.sqlite3.gz')),
            $temporaryDatabasePath,
        );

        $pdo = new PDO('sqlite:'.$temporaryDatabasePath);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $integrityCheck = $pdo->query('PRAGMA integrity_check');

        throw_if(
            $integrityCheck === false || $integrityCheck->fetchColumn() !== 'ok',
            RuntimeException::class,
            'World SQLite database failed integrity check.',
        );

        rename($temporaryDatabasePath, $databasePath);

        $this->components->info('World SQLite database downloaded successfully.');

        return Command::SUCCESS;
    }
}
```

```php
<?php

declare(strict_types=1);

return [

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
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

it('downloads and extracts the world database', function (): void {
    Http::fake([
        config()->string('vetdesk.world_database.download_url') => Http::response(
            File::get(base_path('tests/testfiles/world.sqlite3.gz')),
        ),
    ]);

    $databasePath = storage_path('framework/testing/world.sqlite3');

    config()->set('database.connections.world.database', $databasePath);

    File::delete($databasePath);

    artisan('app:download-world-database')->assertSuccessful();

    $database = new PDO('sqlite:'.$databasePath);

    expect($database->query('PRAGMA integrity_check')->fetchColumn())->toBe('ok');

    File::delete($databasePath);
});

it('skips downloading when the world database already exists', function (): void {
    $databasePath = storage_path('framework/testing/existing-world.sqlite3');

    config()->set('database.connections.world.database', $databasePath);

    File::put($databasePath, 'existing world database');

    artisan('app:download-world-database')
        ->expectsOutputToContain('World SQLite database already exists.')
        ->assertSuccessful();

    expect(File::get($databasePath))->toBe('existing world database');

    File::delete($databasePath);
});

it('preserves the existing world database when a forced download is malformed', function (): void {
    Http::fake([
        config()->string('vetdesk.world_database.download_url') => Http::response(
            File::get(base_path('tests/testfiles/malformed-world.sqlite3.gz')),
        ),
    ]);

    $databasePath = storage_path('framework/testing/invalid-world.sqlite3');

    config()->set('database.connections.world.database', $databasePath);

    File::put($databasePath, 'existing world database');

    expect(fn () => artisan('app:download-world-database', ['--force' => true]))
        ->toThrow(PDOException::class)
        ->and(File::get($databasePath))->toBe('existing world database');

    File::delete($databasePath);
});
```
