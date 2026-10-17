# Enums: Options Concern

Create the `Options` enum concern and the `TranslationKey` meta property, when the project lacks them, to give every translated enum a `translationKey()` method and a static `options()` list built from the translation files.

- The concern needs no test: each enum's exact-label `returns options` test catches missing keys.

```php
<?php

declare(strict_types=1);

namespace App\Enums\MetaProperties;

use ArchTech\Enums\Meta\MetaProperty;
use Attribute;
use Override;

#[Attribute]
final class TranslationKey extends MetaProperty
{
    #[Override]
    public static function method(): string
    {
        return 'translationKey';
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use App\Enums\MetaProperties\TranslationKey;
use ArchTech\Enums\Meta\Meta;
use ArchTech\Enums\Metadata;
use BackedEnum;

/**
 * @method string translationKey()
 */
#[Meta(TranslationKey::class)]
trait Options
{
    use Metadata;

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public static function options(): array
    {
        /** @var array<int, array{label: string, value: string}> */
        return collect(static::cases())->map(fn (BackedEnum $case): array => [
            'label' => trans()->string($case->translationKey()),
            'value' => $case->value,
        ])->all();
    }
}
```
