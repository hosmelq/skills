# Forms: Keep Component Prop Types

Choose types from the target component rather than copying a same-named setter. This focused TextFieldProps example keeps value and defaultValue string-only. Input accepts scalar/list values; Button size is an enum while Input size is numeric. Button and TextField slots are nullable; Input slots are strings.

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Contracts;

interface ProvidesTextFieldProps
{
    /**
     * @return array{
     *     defaultValue?: string,
     *     slot?: null|string,
     *     value?: string,
     * }
     */
    public function toArray(): array;
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Concerns;

trait HasTextFieldProps
{
    /**
     * @var array{
     *     defaultValue?: string,
     *     slot?: null|string,
     *     value?: string,
     * }
     */
    private array $props = [];

    public function defaultValue(string $value): static
    {
        $this->props['defaultValue'] = $value;

        return $this;
    }

    public function slot(null|string $value): static
    {
        $this->props['slot'] = $value;

        return $this;
    }

    /**
     * @return array{
     *     defaultValue?: string,
     *     slot?: null|string,
     *     value?: string,
     * }
     */
    public function toArray(): array
    {
        return $this->props;
    }

    public function value(string $value): static
    {
        $this->props['value'] = $value;

        return $this;
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI;

use App\Forms\Generated\HeroUI\Concerns\HasTextFieldProps;
use App\Forms\Generated\HeroUI\Contracts\ProvidesTextFieldProps;

/**
 * @api
 */
final class TextFieldProps implements ProvidesTextFieldProps
{
    use HasTextFieldProps;
}
```
