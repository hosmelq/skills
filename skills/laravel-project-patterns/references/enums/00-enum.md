# Enums: Backed Enum

Create a string-backed enum for a fixed set of values stored in the database, compared in code or sent to the frontend without translated labels, with `archtechx/enums` helpers.

- Name cases in PascalCase by meaning; use a code as the name only when the code is the meaning, as with currencies.
- Values are snake_case unless they must match an external code or an existing key format, such as dotted `{resource}.{action}` permissions or camelCase frontend keys.
- Every enum uses `Values`. Add `InvokableCases` with an alphabetical `@method static string Case()` line per case when code needs a case's raw string, and pass the case itself wherever an API accepts enums.
- Enums shown to users with labels follow the translated options reference.
- An enum without behavior needs no test.

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Email()
 * @method static string Sms()
 */
enum ReminderChannel: string
{
    use InvokableCases;
    use Values;

    case Email = 'email';
    case Sms = 'sms';
}
```
