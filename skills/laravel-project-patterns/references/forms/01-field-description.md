# Forms: Share a Nullable Description

Use a contract and concern for a nullable field description. The fluent setter keeps null, empty text and supplied text distinct; the getter returns the stored value.

```php
<?php

declare(strict_types=1);

namespace App\Forms\Fields\Contracts;

interface ProvidesDescription
{
    public function description(null|string $value): static;

    public function getDescription(): null|string;
}
```

```php
<?php

declare(strict_types=1);

namespace App\Forms\Fields\Concerns;

trait HasDescription
{
    private null|string $description = null;

    public function description(null|string $value): static
    {
        $this->description = $value;

        return $this;
    }

    public function getDescription(): null|string
    {
        return $this->description;
    }
}
```
