# Forms: Guard Numeric and List Props

Guard nonfinite scalar floats before updating numeric or scalar/list props. Zero, empty strings and empty arrays survive. The `list<string>` docblock is static: these setters do not validate array keys or elements, or inspect numeric strings. Use a component-specific contract.

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Contracts;

interface ProvidesInputProps
{
    /**
     * @return array{
     *     'aria-label'?: string,
     *     max?: float|int|string,
     *     size?: float|int,
     *     value?: float|int|list<string>|string,
     * }
     */
    public function toArray(): array;
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Concerns;

use InvalidArgumentException;

trait HasInputProps
{
    /**
     * @var array{
     *     'aria-label'?: string,
     *     max?: float|int|string,
     *     size?: float|int,
     *     value?: float|int|list<string>|string,
     * }
     */
    private array $props = [];

    public function ariaLabel(string $value): static
    {
        $this->props['aria-label'] = $value;

        return $this;
    }

    public function max(float|int|string $value): static
    {
        throw_if(
            is_float($value) && ! is_finite($value),
            InvalidArgumentException::class,
            'max must be finite',
        );

        $this->props['max'] = $value;

        return $this;
    }

    public function size(float|int $value): static
    {
        throw_if(
            is_float($value) && ! is_finite($value),
            InvalidArgumentException::class,
            'size must be finite',
        );

        $this->props['size'] = $value;

        return $this;
    }

    /**
     * @return array{
     *     'aria-label'?: string,
     *     max?: float|int|string,
     *     size?: float|int,
     *     value?: float|int|list<string>|string,
     * }
     */
    public function toArray(): array
    {
        return $this->props;
    }

    /**
     * @param float|int|list<string>|string $value
     */
    public function value(array|float|int|string $value): static
    {
        throw_if(
            is_float($value) && ! is_finite($value),
            InvalidArgumentException::class,
            'value must be finite',
        );

        $this->props['value'] = $value;

        return $this;
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI;

use App\Forms\Generated\HeroUI\Concerns\HasInputProps;
use App\Forms\Generated\HeroUI\Contracts\ProvidesInputProps;

/**
 * @api
 */
final class InputProps implements ProvidesInputProps
{
    use HasInputProps;
}
```

`defaultValue()` uses the same union and guard for Input, TextArea, Label and Description. `key()` also permits null; see [prop objects](02-prop-object.md).
