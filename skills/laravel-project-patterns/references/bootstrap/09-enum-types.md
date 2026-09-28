# Providers: Generate Enum Types

Configure Spatie TypeScript Transformer v3 to collect application enums and write a flat module. Keep the input directory, output directory and generated filename consistent with the frontend imports.

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider as BaseProvider;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;

class TypeScriptTransformerServiceProvider extends BaseProvider
{
    /**
     * Generate PHP enums as TypeScript union types for the frontend.
     */
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
