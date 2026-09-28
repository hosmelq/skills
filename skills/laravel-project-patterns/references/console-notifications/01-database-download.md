# Console: Download and Validate a Database

Skip an existing file unless `--force` is set. Download and decompress into a temporary sibling directory, then check SQLite integrity before replacing the target. Keep the configured parent directory writable. Integrity does not validate the application's schema; the early return does not validate an existing file. Failures propagate before the success response.

Requires `spatie/temporary-directory`, `thecodingmachine/safe`, PDO SQLite and zlib. Set `database.connections.catalog.database` and `catalog-database.download_url`.

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use function Safe\copy;
use function Safe\rename;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use PDO;
use RuntimeException;
use Spatie\TemporaryDirectory\TemporaryDirectory;

#[Description('Download the Catalog SQLite database.')]
#[Signature('app:download-catalog-database {--force : Download even if the database exists}')]
class DownloadCatalogDatabaseCommand extends Command
{
    public function handle(): int
    {
        $databasePath = Config::string('database.connections.catalog.database');

        if (File::exists($databasePath) && ! $this->option('force')) {
            $this->components->info('Catalog SQLite database already exists.');

            return Command::SUCCESS;
        }

        $tmp = new TemporaryDirectory(dirname($databasePath))
            ->deleteWhenDestroyed()
            ->create();

        $temporaryDatabasePath = $tmp->path('catalog.sqlite3');

        Http::retry(3, 500)
            ->sink($tmp->path('catalog.sqlite3.gz'))
            ->get(Config::string('catalog-database.download_url'))
            ->throw();

        copy(
            sprintf('compress.zlib://%s', $tmp->path('catalog.sqlite3.gz')),
            $temporaryDatabasePath
        );

        $pdo = new PDO('sqlite:'.$temporaryDatabasePath);

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $integrityCheck = $pdo->query('PRAGMA integrity_check');

        throw_if(
            $integrityCheck === false || $integrityCheck->fetchColumn() !== 'ok',
            RuntimeException::class,
            'Catalog SQLite database failed integrity check.'
        );

        rename($temporaryDatabasePath, $databasePath);

        $this->components->info('Catalog SQLite database downloaded successfully.');

        return Command::SUCCESS;
    }
}
```

See [download tests](../tests/console/00-console-test-order.md) and [Artisan](https://laravel.com/docs/13.x/artisan).
