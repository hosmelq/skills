# Enums: Literal Alphabet Values

Implement an alphabet enum whose backing values are the complete character strings used by an identifier generator. Preserve case and character order exactly.

Here `values()` and callable cases return lowercase alphabets directly. The symbolic alphabet mapping is a separate example.

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Alphanumeric()
 * @method static string Letters()
 * @method static string Numbers()
 */
enum NanoIDAlphabet: string
{
    use InvokableCases;
    use Values;

    case Alphanumeric = 'abcdefghijklmnopqrstuvwxyz0123456789';
    case Letters = 'abcdefghijklmnopqrstuvwxyz';
    case Numbers = '0123456789';
}
```
