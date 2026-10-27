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
