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
