# Forms: Preserve Boolean and Enum Props

A boolean-or-enum setter preserves boolean false separately from the string false backing value. Keep exact frontend keys and explicit nullable slots. This focused ButtonProps example includes only the demonstrated props; retain the target component contract when extending it.

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Contracts;

interface ProvidesButtonProps
{
    /**
     * @return array{
     *     'aria-pressed'?: 'false'|'mixed'|'true'|bool,
     *     isDisabled?: bool,
     *     size?: 'lg'|'md'|'sm',
     *     slot?: null|string,
     *     type?: 'button'|'reset'|'submit',
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

use App\Forms\Generated\HeroUI\Enums\ButtonAriaPressed;
use App\Forms\Generated\HeroUI\Enums\ButtonSize;
use App\Forms\Generated\HeroUI\Enums\ButtonType;

trait HasButtonProps
{
    /**
     * @var array{
     *     'aria-pressed'?: 'false'|'mixed'|'true'|bool,
     *     isDisabled?: bool,
     *     size?: 'lg'|'md'|'sm',
     *     slot?: null|string,
     *     type?: 'button'|'reset'|'submit',
     *     value?: string,
     * }
     */
    private array $props = [];

    public function ariaPressed(bool|ButtonAriaPressed $value): static
    {
        $this->props['aria-pressed'] = $value instanceof ButtonAriaPressed ? $value->value : $value;

        return $this;
    }

    public function isDisabled(bool $value): static
    {
        $this->props['isDisabled'] = $value;

        return $this;
    }

    public function size(ButtonSize $value): static
    {
        $this->props['size'] = $value->value;

        return $this;
    }

    public function slot(null|string $value): static
    {
        $this->props['slot'] = $value;

        return $this;
    }

    /**
     * @return array{
     *     'aria-pressed'?: 'false'|'mixed'|'true'|bool,
     *     isDisabled?: bool,
     *     size?: 'lg'|'md'|'sm',
     *     slot?: null|string,
     *     type?: 'button'|'reset'|'submit',
     *     value?: string,
     * }
     */
    public function toArray(): array
    {
        return $this->props;
    }

    public function type(ButtonType $value): static
    {
        $this->props['type'] = $value->value;

        return $this;
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

use App\Forms\Generated\HeroUI\Concerns\HasButtonProps;
use App\Forms\Generated\HeroUI\Contracts\ProvidesButtonProps;

/**
 * @api
 */
final class ButtonProps implements ProvidesButtonProps
{
    use HasButtonProps;
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Enums;

enum ButtonAriaPressed: string
{
    case False = 'false';
    case Mixed = 'mixed';
    case True = 'true';
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Enums;

enum ButtonSize: string
{
    case Lg = 'lg';
    case Md = 'md';
    case Sm = 'sm';
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Generated\HeroUI\Enums;

enum ButtonType: string
{
    case Button = 'button';
    case Reset = 'reset';
    case Submit = 'submit';
}
```
