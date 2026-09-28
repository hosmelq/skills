# Providers: Configure Application Defaults

Use the relevant methods in the application provider. Model strictness and unguarding are separate choices; production violation reporters replace the default handlers. Preserve environment checks for passwords and HTTPS.

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Sentry\Laravel\Integration;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureDates();
        $this->configureFormRequests();
        $this->configureModels();
        $this->configurePasswordValidation();
        $this->configureResources();
        $this->configureUrls();
        $this->configureVite();
    }

    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    private function configureFormRequests(): void
    {
        FormRequest::failOnUnknownFields();
    }

    private function configureModels(): void
    {
        Model::automaticallyEagerLoadRelationships();
        Model::shouldBeStrict();
        Model::unguard();

        Relation::enforceMorphMap([
            'user' => User::class,
        ]);

        if ($this->app->isProduction()) {
            Model::handleDiscardedAttributeViolationUsing(
                Integration::discardedAttributeViolationReporter()
            );
            Model::handleLazyLoadingViolationUsing(
                Integration::lazyLoadingViolationReporter()
            );
            Model::handleMissingAttributeViolationUsing(
                Integration::missingAttributeViolationReporter()
            );
        }
    }

    private function configurePasswordValidation(): void
    {
        Password::defaults(
            fn () => app()->isProduction() ? Password::min(8)->uncompromised() : Password::min(8)
        );
    }

    private function configureResources(): void
    {
        JsonResource::withoutWrapping();
    }

    private function configureUrls(): void
    {
        URL::forceHttps(! $this->app->environment('local', 'testing'));
    }

    private function configureVite(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
```

Additional boot methods: [development](03-development-commands.md), [health](05-health-checks.md), [rate limits](04-rate-limiters.md), [redirect toast](../support/06-redirect-toast.md).
