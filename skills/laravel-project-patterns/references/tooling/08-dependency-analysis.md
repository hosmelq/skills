# Analyze Composer Dependencies and Runtime Integrations

Use dependency analysis with migrations classified as production code. Suppress only justified runtime integrations and extension findings; this example’s allowlist is not evidence that another project needs the same exceptions.

```php
<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return new Configuration()
    ->addPathToScan(__DIR__.'/database/migrations', isDev: false)
    ->ignoreErrors([ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnExtensions([
        'ext-bcmath',
        'ext-zlib',
    ], [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackages([
        'inertiaui/modal',
        'laravel/octane',
        'laravel/slack-notification-channel',
        'laravel/tinker',
        'laravel/wayfinder',
        'league/flysystem-aws-s3-v3',
        'propaganistas/laravel-disposable-email',
        'resend/resend-php',
        'tpetry/laravel-query-expressions',
    ], [ErrorType::UNUSED_DEPENDENCY]);
```
