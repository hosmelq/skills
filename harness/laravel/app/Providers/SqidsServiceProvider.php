<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Override;
use Sqids\Sqids;
use Sqids\SqidsInterface;

final class SqidsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return list<class-string>
     */
    #[Override]
    public function provides(): array
    {
        return [SqidsInterface::class];
    }

    #[Override]
    public function register(): void
    {
        $this->app->singleton(fn (): SqidsInterface => new Sqids(
            minLength: config()->integer('vetdesk.sqids.min_length'),
        ));
    }
}
