# Exceptions: Field Errors

Carry the validation field as a public readonly string and pass the message to the parent. Keep creation and update as distinct types. An open-ended range targets `maximum_weight`; an overlap targets `minimum_weight`.

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class CannotCreatePlanRate extends Exception
{
    public function __construct(public readonly string $field, string $message)
    {
        parent::__construct($message);
    }

    public static function becauseItHasAnOpenEndedRate(): self
    {
        return new self('maximum_weight', 'Only one open-ended rate is allowed per plan rule.');
    }

    public static function becauseItOverlapsAnExistingRate(): self
    {
        return new self('minimum_weight', 'The weight range overlaps an existing rate.');
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class CannotUpdatePlanRate extends Exception
{
    public function __construct(public readonly string $field, string $message)
    {
        parent::__construct($message);
    }

    public static function becauseItHasAnOpenEndedRate(): self
    {
        return new self('maximum_weight', 'Only one open-ended rate is allowed per plan rule.');
    }

    public static function becauseItOverlapsAnExistingRate(): self
    {
        return new self('minimum_weight', 'The weight range overlaps an existing rate.');
    }
}
```

Use the [controller field mapping](../controllers/23-range-errors.md) when converting these exceptions to validation errors.
