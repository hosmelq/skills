# Support: Redirect Toast Macro

Controllers using `back()->toast()` need this redirect macro. Register the focused provider in `bootstrap/providers.php`, or put its registration in an existing provider’s `boot()`. Keep the returned redirect instance: the global helper returns `void` and cannot replace the fluent macro. Reuse the enums from the [toast payload example](05-toast-flash.md).

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\FlashKey;
use App\Enums\ToastVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class ToastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RedirectResponse::macro('toast', function (
            string $title,
            null|string $description = null,
            ToastVariant $variant = ToastVariant::Success,
            int $timeout = 5
        ): RedirectResponse {
            Inertia::flash(FlashKey::Toast(), array_filter([
                'description' => $description,
                'timeout' => $timeout * 1000,
                'title' => $title,
                'variant' => $variant->value,
            ], fn (null|int|string $value): bool => $value !== null));

            return $this;
        });
    }
}
```
