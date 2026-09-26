# Enums: Backed Values and Callable Cases

Implement string-backed enum code with declaration-order values(). Add InvokableCases only where callers use Case() to obtain its backing string.

The first form needs only `Values`. Case names and backing strings are independent contracts; they may coincide.

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\Values;

enum DisplayMode: string
{
    use Values;

    case Compact = 'compact';
    case Expanded = 'expanded';
    case Hidden = 'hidden';
}
```

`MessageKey::Notice` is an enum case; `MessageKey::Notice()` and invoking that case return its backing string. Add `@method static string Case()` for each callable case.

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Notice()
 */
enum MessageKey: string
{
    use InvokableCases;
    use Values;

    case Notice = 'notice';
}
```
