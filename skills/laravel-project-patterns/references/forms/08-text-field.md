# Forms: Serialize a Text Field

Keep input, text-area and wrapper props separate. Preserve parent fields, nullable label and description, and each prop object. Add value only when the name is strictly null; an empty name does not take that branch. Component registration must match TextField.

```php
<?php

declare(strict_types=1);

namespace App\Forms\Fields;

use App\Forms\Fields\Concerns\HasDescription;
use App\Forms\Fields\Contracts\ProvidesDescription;
use App\Forms\Generated\HeroUI\InputProps;
use App\Forms\Generated\HeroUI\TextAreaProps;
use App\Forms\Generated\HeroUI\TextFieldProps;
use InertiaUI\Forms\Fields\Concerns\HasLabel;
use InertiaUI\Forms\Fields\Contracts\ProvidesLabel;
use InertiaUI\Forms\Fields\Field;
use Override;

class TextField extends Field implements ProvidesDescription, ProvidesLabel
{
    use HasDescription;
    use HasLabel;

    private null|InputProps $input = null;

    private null|TextFieldProps $props = null;

    private null|TextAreaProps $textArea = null;

    #[Override]
    public function getComponent(): string
    {
        return 'TextField';
    }

    public function input(InputProps $props): static
    {
        $this->input = $props;

        return $this;
    }

    public function props(TextFieldProps $props): static
    {
        $this->props = $props;

        return $this;
    }

    public function textArea(TextAreaProps $props): static
    {
        $this->textArea = $props;

        return $this;
    }

    #[Override]
    public function toArray(): array
    {
        return [
            ...parent::toArray(),
            'description' => $this->getDescription(),
            'input' => $this->input?->toArray(),
            'label' => $this->getLabel(),
            'props' => $this->props?->toArray(),
            'textArea' => $this->textArea?->toArray(),
            ...($this->getName() === null ? ['value' => $this->getValue(null)] : []),
        ];
    }
}
```

Requires the [description concern](01-field-description.md) and project-generated prop classes. The frontend chooses TextArea when supplied and Input otherwise; PHP stores both independently.
