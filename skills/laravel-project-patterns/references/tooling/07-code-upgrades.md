# Configure Automated PHP Refactoring

Use Rector with the project’s PHP target and installed Laravel/Pest sets. Keep the cache, app/test paths and explicit rule exclusions. A path-scoped skip disables only that rule on matching files.

```php
<?php

declare(strict_types=1);

use Pest\Rector\Set\PestSetList;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withCache('./.cache/rector', FileCacheStorage::class)
    ->withComposerBased(laravel: true)
    ->withImportNames()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withPhpSets(php85: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        instanceOf: true,
        earlyReturn: true,
        carbon: true,
        rectorPreset: true
    )
    ->withSets([
        LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
        LaravelSetList::LARAVEL_IF_HELPERS,
        PestSetList::CODING_STYLE,
    ])
    ->withSkip([
        __DIR__.'/bootstrap/cache',
        CatchExceptionNameMatchingTypeRector::class,
        ClosureToArrowFunctionRector::class,
        ArrayToFirstClassCallableRector::class,
        StrictArrayParamDimFetchRector::class => [
            __DIR__.'/app/*/*ServiceProvider.php',
        ],
    ]);
```
