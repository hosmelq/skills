# Forms: Build a Typed Prop Object

Compose a final prop object from a contract and fluent trait. Omit unset keys while retaining explicit false, null and empty strings. Check finite floats before assignment; serialize enums to their backing values.

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Contracts;

interface ProvidesFieldErrorProps
{
    /**
     * @return array{
     *     className?: string,
     *     dir?: string,
     *     elementType?: string,
     *     hidden?: bool,
     *     id?: string,
     *     inert?: bool,
     *     key?: null|float|int|string,
     *     lang?: string,
     *     translate?: 'no'|'yes',
     * }
     */
    public function toArray(): array;
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Concerns;

use App\Forms\Generated\HeroUI\Enums\FieldErrorTranslate;
use InvalidArgumentException;

trait HasFieldErrorProps
{
    /**
     * @var array{
     *     className?: string,
     *     dir?: string,
     *     elementType?: string,
     *     hidden?: bool,
     *     id?: string,
     *     inert?: bool,
     *     key?: null|float|int|string,
     *     lang?: string,
     *     translate?: 'no'|'yes',
     * }
     */
    private array $props = [];

    public function className(string $value): static
    {
        $this->props['className'] = $value;

        return $this;
    }

    public function dir(string $value): static
    {
        $this->props['dir'] = $value;

        return $this;
    }

    public function elementType(string $value): static
    {
        $this->props['elementType'] = $value;

        return $this;
    }

    public function hidden(bool $value): static
    {
        $this->props['hidden'] = $value;

        return $this;
    }

    public function id(string $value): static
    {
        $this->props['id'] = $value;

        return $this;
    }

    public function inert(bool $value): static
    {
        $this->props['inert'] = $value;

        return $this;
    }

    public function key(null|float|int|string $value): static
    {
        throw_if(
            is_float($value) && ! is_finite($value),
            InvalidArgumentException::class,
            'key must be finite',
        );

        $this->props['key'] = $value;

        return $this;
    }

    public function lang(string $value): static
    {
        $this->props['lang'] = $value;

        return $this;
    }

    /**
     * @return array{
     *     className?: string,
     *     dir?: string,
     *     elementType?: string,
     *     hidden?: bool,
     *     id?: string,
     *     inert?: bool,
     *     key?: null|float|int|string,
     *     lang?: string,
     *     translate?: 'no'|'yes',
     * }
     */
    public function toArray(): array
    {
        return $this->props;
    }

    public function translate(FieldErrorTranslate $value): static
    {
        $this->props['translate'] = $value->value;

        return $this;
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI;

use App\Forms\Generated\HeroUI\Concerns\HasFieldErrorProps;
use App\Forms\Generated\HeroUI\Contracts\ProvidesFieldErrorProps;

/**
 * @api
 */
final class FieldErrorProps implements ProvidesFieldErrorProps
{
    use HasFieldErrorProps;
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Enums;

enum FieldErrorTranslate: string
{
    case No = 'no';
    case Yes = 'yes';
}
```
