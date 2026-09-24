# Model Tests: Code Normalization

Pure model normalization: dash, lowercase, mixed punctuation, no separator and space variants produce one canonical value; punctuation-only input becomes empty.

```php
<?php

declare(strict_types=1);

use App\Models\Cabinet;

it('normalizes codes', function (string $code): void {
    expect(Cabinet::normalizeCode($code))->toBe('DEMO704218');
})->with([
    'dash separator' => 'DEMO-704218',
    'lowercase' => 'demo-704218',
    'mixed separators' => 'demo.704/218',
    'no separator' => 'DEMO704218',
    'space separator' => 'DEMO 704218',
]);

it('normalizes non-code input to an empty string', function (): void {
    expect(Cabinet::normalizeCode(' - / '))->toBeEmpty();
});
```
