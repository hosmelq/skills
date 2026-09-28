# Configuration: Media Storage and Conversions

Merge these entries into `config/media-library.php`. The original and conversion disks are separate; queueing conversions and dispatching after commit are separate flags. Keep custom file and path strategies consistent with the stored media.

```php
<?php

declare(strict_types=1);

use App\Support\Media\FileNamer;
use App\Support\Media\PathGenerator;
use Spatie\MediaLibrary\Downloaders\HttpFacadeDownloader;

return [
    'conversions_disk_name' => env('MEDIA_CONVERSIONS_DISK'),
    'disk_name' => env('MEDIA_DISK', 'public'),
    'file_namer' => FileNamer::class,
    'force_lazy_loading' => env('FORCE_MEDIA_LIBRARY_LAZY_LOADING', true),
    'max_file_size' => 1024 * 1024 * 10,
    'media_downloader' => HttpFacadeDownloader::class,
    'path_generator' => PathGenerator::class,
    'queue_connection_name' => env('QUEUE_CONNECTION', 'sync'),
    'queue_conversions_after_database_commit' => env('QUEUE_CONVERSIONS_AFTER_DB_COMMIT', true),
    'queue_conversions_by_default' => env('QUEUE_CONVERSIONS_BY_DEFAULT', true),
    'queue_name' => env('MEDIA_QUEUE', ''),
    'use_default_collection_serialization' => false,
];
```

Implement [file names](../support/01-media-file-name.md) and [paths](../support/02-media-path.md).
