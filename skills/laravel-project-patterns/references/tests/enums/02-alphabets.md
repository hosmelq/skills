# Enum Tests: Alphabet Mapping

Unit test for an enum case's `alphabet()` mapping to exact character strings. Preserve case, character order and every dataset row; this tests a method, not the enum's backed values or an ID generator.

```php
<?php

declare(strict_types=1);

use App\Enums\CodeAlphabet;

it('defines alphabets', function (CodeAlphabet $codeAlphabet, string $alphabet): void {
    expect($codeAlphabet->alphabet())->toBe($alphabet);
})->with([
    'alphanumeric' => [CodeAlphabet::Alphanumeric, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'],
    'letters' => [CodeAlphabet::Letters, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'],
    'numbers' => [CodeAlphabet::Numbers, '0123456789'],
]);
```
