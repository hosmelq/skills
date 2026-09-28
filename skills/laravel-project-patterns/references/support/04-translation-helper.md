# Support: String Translations

Keep replacement values and the optional locale intact when calling `trans()`. This wrapper requires a string translation; an array translation fails with `AssertionError` when assertions are enabled or `TypeError` from the return type when disabled. Autoload this function from `app/functions.php` through Composer’s `autoload.files`.

```php
<?php

declare(strict_types=1);

namespace App;

/**
 * Wraps Laravel's translation helper so the return type stays narrowed to string.
 *
 * @param array<string, null|bool|float|int|string> $replace
 */
function __(string $key, array $replace = [], null|string $locale = null): string
{
    $message = trans($key, $replace, $locale);

    assert(is_string($message));

    return $message;
}
```
