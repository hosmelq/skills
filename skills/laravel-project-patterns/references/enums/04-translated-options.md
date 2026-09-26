# Enums: Translation Metadata and Options

Implement the local Options trait for string-backed enums: translate per-case metadata into a declaration-order list of label/value pairs. Register TranslationKey through ArchTech metadata.

Requires an autoloaded translation function returning `string` (`App\__` here); import the project's inspected equivalent. Give every case a `TranslationKey` and use `Options` directly on the enum: ArchTech reads metadata from direct traits. Provide string translations for every key; translate at call time.

```php
<?php

declare(strict_types=1);

namespace App\Enums\MetaProperties;

use ArchTech\Enums\Meta\MetaProperty;
use Attribute;
use Override;

#[Attribute]
class TranslationKey extends MetaProperty
{
    #[Override]
    public static function method(): string
    {
        return 'translationKey';
    }
}
```

The local `App\Enums\Concerns\Options` returns pairs; `ArchTech\Enums\Options` returns a different shape and is not a replacement.

```php
<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use function App\__;

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
            'label' => __($case->translationKey()),
            'value' => $case->value,
        ])->all();
    }
}
```

This enum returns `CA`, then `US`, with labels from `country.codes.CA` and `country.codes.US` in the current locale. Translation keys are literal and keep their casing.

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\Options;
use App\Enums\MetaProperties\TranslationKey;
use ArchTech\Enums\Comparable;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Canada()
 * @method static string UnitedStates()
 * @method static string[] values()
 */
enum CountryCode: string
{
    use Comparable;
    use InvokableCases;
    use Options;
    use Values;

    #[TranslationKey('country.codes.CA')]
    case Canada = 'CA';

    #[TranslationKey('country.codes.US')]
    case UnitedStates = 'US';
}
```
