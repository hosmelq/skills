# Support: Toast Flash Data

Flash a `toast` payload through Inertia with title, optional description, variant backing value and timeout converted from seconds to milliseconds. Remove only null values: keep an empty title/description and timeout `0`. Add this function to the same autoloaded `app/functions.php`; reuse the enums below.

```php
<?php

declare(strict_types=1);

namespace App;

use App\Enums\FlashKey;
use App\Enums\ToastVariant;
use Inertia\Inertia;

function toast(
    string $title,
    null|string $description = null,
    ToastVariant $variant = ToastVariant::Success,
    int $timeout = 5
): void {
    Inertia::flash(FlashKey::Toast(), array_filter([
        'description' => $description,
        'timeout' => $timeout * 1000,
        'title' => $title,
        'variant' => $variant->value,
    ], fn (null|int|string $value): bool => $value !== null));
}
```

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\InvokableCases;
use ArchTech\Enums\Values;

/**
 * @method static string Toast()
 */
enum FlashKey: string
{
    use InvokableCases;
    use Values;

    case Toast = 'toast';
}
```

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use ArchTech\Enums\Values;

enum ToastVariant: string
{
    use Values;

    case Accent = 'accent';
    case Danger = 'danger';
    case Default = 'default';
    case Success = 'success';
    case Warning = 'warning';
}
```

For `back()->toast()`, use the [redirect macro](06-redirect-toast.md). This global function is called as `App\toast(...)`. Use the request’s configured Inertia/session integration; this helper only builds and flashes the payload. [Inertia flash data](https://inertiajs.com/docs/v3/data-props/flash-data).
