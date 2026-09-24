# Action Tests: Resolve an Active Parent

Integration tests for a guarded parent resolver: reject inactivity, observe row-lock SQL and return the active record. Query observation does not demonstrate concurrent lock contention.

Use the inspected test helper or the [query observer](01-row-lock-observation.md); it must be registered before invoking the action.

```php
<?php

declare(strict_types=1);

use App\Actions\ServicePlans\EnsureActiveServicePlan;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\ServicePlan;

it('rejects deactivated parent records', function (): void {
    $servicePlan = ServicePlan::factory()->deactivated()->createOne();

    expect(fn () => resolve(EnsureActiveServicePlan::class)->handle($servicePlan))
        ->toThrow(CannotUseDeactivatedServicePlan::class, 'Cannot use a deactivated service plan.');
});

it('observes a row-lock query', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    assertRowLockQueryObserved($servicePlan);

    resolve(EnsureActiveServicePlan::class)->handle($servicePlan);
});

it('resolves an active parent record', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    $resolvedServicePlan = resolve(EnsureActiveServicePlan::class)
        ->handle($servicePlan);

    expect($resolvedServicePlan->is($servicePlan))->toBeTrue();
});
```
