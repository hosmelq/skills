# Enum Tests: Label Mapping

Unit test for an enum case's `label()` mapping to exact display text. Keep an explicit expected label for every case; labels need not be title-cased case names or alphabet strings.

```php
<?php

declare(strict_types=1);

use App\Enums\CodeAlphabet;

it('defines labels', function (CodeAlphabet $codeAlphabet, string $label): void {
    expect($codeAlphabet->label())->toBe($label);
})->with([
    'alphanumeric' => [CodeAlphabet::Alphanumeric, 'Letters and digits'],
    'letters' => [CodeAlphabet::Letters, 'Letters (A-Z)'],
    'numbers' => [CodeAlphabet::Numbers, 'Digits (0-9)'],
]);
```
