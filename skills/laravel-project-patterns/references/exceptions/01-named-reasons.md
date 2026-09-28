# Exceptions: Named Reasons

Return `self` from named static factories. Keep separate exception types where callers catch them separately; multiple reasons can share one type. Factories construct exceptions; the caller checks the condition and throws.

```php
<?php

declare(strict_types=1);

namespace App\Exceptions\WorkOrders;

use Exception;

class MemberIsUnavailable extends Exception
{
    public static function becauseItIsUnavailable(): self
    {
        return new self('The selected member is unavailable.');
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class CannotReviewEnrollment extends Exception
{
    public static function becauseItWasApproved(): self
    {
        return new self('An approved enrollment cannot be rejected.');
    }

    public static function becauseItWasRejected(): self
    {
        return new self('A rejected enrollment cannot be approved.');
    }
}
```
