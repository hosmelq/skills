# Providers: Register a Deferred Binding

List both the class and its string alias in `provides()`. Register one singleton and alias the same binding; resolving either name reaches the same instance.

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Hidehalo\Nanoid\Client;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Override;

class NanoIDServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return list<string>
     */
    #[Override]
    public function provides(): array
    {
        return [Client::class, 'nanoid'];
    }

    #[Override]
    public function register(): void
    {
        $this->app->singleton(Client::class);

        $this->app->alias(Client::class, 'nanoid');
    }
}
```

The [Sqids interface binding](../support/03-sqid-codec.md) additionally uses typed configuration.
