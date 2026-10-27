# Providers: TypeScript Transformer

Generate TypeScript union types for every PHP enum with Spatie's TypeScript transformer, so the frontend shares enum values with the backend.

- Register the provider in `bootstrap/providers.php`; the output directory must exist before the first run.
- Add a `typescript` Composer script that runs `php artisan typescript:transform --no-interaction`, and run it before frontend builds.
- Commit the output directory, not the generated files.

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Override;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;

final class TypeScriptTransformerServiceProvider extends TypeScriptTransformerApplicationServiceProvider
{
    /**
     * Generate PHP enums as TypeScript union types for the frontend.
     */
    #[Override]
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config
            ->transformer(EnumTransformer::class)
            ->transformDirectories(app_path('Enums'))
            ->outputDirectory(resource_path('js/types'))
            ->writer(new FlatModuleWriter('generated/enums.ts'));
    }
}
```
