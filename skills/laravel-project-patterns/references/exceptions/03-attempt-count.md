# Exceptions: Attempt Count

Include the supplied attempt count in the message with `sprintf`. The caller owns retries and the limit; this factory only constructs the failure.

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class CannotGenerateCabinetCode extends Exception
{
    public static function maxAttempts(int $attempts): self
    {
        return new self(
            sprintf('Unable to generate unique cabinet code after %d attempts.', $attempts)
        );
    }
}
```

See the [bounded generator](../actions/29-generate-code.md) for the throw site.
