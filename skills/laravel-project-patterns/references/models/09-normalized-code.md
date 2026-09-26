# Models: Code Normalization

Implement an explicit static code normalizer that uppercases input and removes every character outside ASCII A–Z and 0–9. It does not persist or assign a normalized field.

Keep the transformation order. The caller assigns the normalized value when its inspected write contract requires it.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cabinet extends Model
{
    public static function normalizeCode(string $code): string
    {
        return Str::of($code)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->toString();
    }
}
```
