# Forms: Serialize a Submit Field

Extend the InertiaUI Forms Field and its label concern. make() treats its argument as the label. Match getComponent() to frontend registration, retain parent serialization, and distinguish absent props (null) from an attached empty prop object ([]).

```php
<?php

declare(strict_types=1);

namespace App\Forms\Fields;

use App\Forms\Generated\HeroUI\ButtonProps;
use InertiaUI\Forms\Fields\Concerns\HasLabel;
use InertiaUI\Forms\Fields\Contracts\ProvidesLabel;
use InertiaUI\Forms\Fields\Field;
use Override;

class SubmitButton extends Field implements ProvidesLabel
{
    use HasLabel;

    private null|ButtonProps $props = null;

    #[Override]
    public static function make(null|string $label = null): static
    {
        return parent::make()->label($label);
    }

    #[Override]
    public function getComponent(): string
    {
        return 'SubmitButton';
    }

    public function props(ButtonProps $props): static
    {
        $this->props = $props;

        return $this;
    }

    #[Override]
    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'label' => $this->getLabel(),
            'props' => $this->props?->toArray(),
        ];
    }
}
```

The frontend must register `SubmitButton`. Use the project-generated `ButtonProps`; [enum props](03-enum-props.md) shows its focused serialization pattern.
