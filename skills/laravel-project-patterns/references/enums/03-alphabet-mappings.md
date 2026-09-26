# Enums: Alphabet and Label Mappings

Implement a symbolic code-alphabet enum with independent alphabet() and label() match expressions, plus Comparable, InvokableCases and Values helpers.

Backing values identify choices; `alphabet()` returns exact uppercase characters and `label()` returns explicit display text. Keep both matches exhaustive. `Comparable` supplies case comparisons such as `is()` and `in()`.

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\Comparable;
use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Alphanumeric()
 * @method static string Letters()
 * @method static string Numbers()
 */
enum CodeAlphabet: string
{
    use Comparable;
    use InvokableCases;
    use Values;

    case Alphanumeric = 'alphanumeric';
    case Letters = 'letters';
    case Numbers = 'numbers';

    public function alphabet(): string
    {
        return match ($this) {
            self::Alphanumeric => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            self::Letters => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            self::Numbers => '0123456789',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Alphanumeric => 'Letters and digits',
            self::Letters => 'Letters (A-Z)',
            self::Numbers => 'Digits (0-9)',
        };
    }
}
```
