# Exceptions: Previous Cause

Accept a nullable `QueryException` and forward it with `previous:`. A proactive duplicate check supplies no cause; a caught matching constraint failure supplies the original exception. Preserve unrelated query errors when handling the failure.

```php
<?php

declare(strict_types=1);

namespace App\Exceptions\WorkOrders;

use Exception;
use Illuminate\Database\QueryException;

class WorkOrderReferenceAlreadyExists extends Exception
{
    public static function becauseItIsAlreadyInUse(null|QueryException $previous = null): self
    {
        return new self('The work order reference already exists.', previous: $previous);
    }
}
```

The [related-record action](../actions/38-create-related-record.md) shows how the caller identifies the constraint.
