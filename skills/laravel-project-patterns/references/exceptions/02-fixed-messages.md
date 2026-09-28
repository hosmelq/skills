# Exceptions: Fixed Messages

Use a public zero-argument constructor for one fixed message and call the parent constructor. Preserve whether the inspected exception is extensible or `final`.

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class CannotDeleteInitialWorkOrderStatus extends Exception
{
    public function __construct()
    {
        parent::__construct('The team must keep an active initial work order status.');
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class CannotDeleteMemberWithWorkOrderHistory extends Exception
{
    public function __construct()
    {
        parent::__construct('Cannot delete a member with work orders.');
    }
}
```
