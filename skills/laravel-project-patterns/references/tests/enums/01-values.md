# Enum Tests: Available Values

Unit test for the complete literal list returned by a string-backed enum's `values()`. With `ArchTech\Enums\Values`, this is declaration order, which may differ from custom display order.

Use the inspected values unchanged, including full character strings for alphabet-valued enums; do not sort, normalize or derive the expected list.

```php
<?php

declare(strict_types=1);

use App\Enums\DisplayMode;

it('defines available values', function (): void {
    expect(DisplayMode::values())->toEqual([
        'compact',
        'expanded',
        'hidden',
    ]);
});
```
