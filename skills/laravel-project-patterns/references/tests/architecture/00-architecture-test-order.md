# Architecture Tests: Ordered Checklist and Complete Example

Pest architecture rules: PHP, Laravel and security presets, followed by generated-enum and strict-types expectations.

Keep the declarations at file scope in this order:

1. PHP preset.
2. Laravel preset with scoped exclusions.
3. Security preset with the inspected function exception.
4. `generated enums`: check the generated namespace excluded from the Laravel preset.
5. `strict types`: check the application namespace.

Adapt the illustrated exclusions only to exceptions required by the inspected suite. Each `ignoring()` applies to its preset; the separate namespace rules remain active.

```php
<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;

arch()->preset()->php();
arch()->preset()->laravel()->ignoring([
    'App\Enums\FieldDefinitions',
    'App\Generated\Enums',
    AppServiceProvider::class,
]);
arch()->preset()->security()->ignoring('assert');

arch('generated enums')->expect('App\Generated\Enums')->toBeEnums();

arch('strict types')->expect('App')->toUseStrictTypes();
```
