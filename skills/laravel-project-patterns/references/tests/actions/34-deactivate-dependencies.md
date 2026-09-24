# Action Tests: Deactivate with Dependent Records

Integration tests for parent deactivation: reject active dependents, observe row-lock SQL, persist deactivation and allow inactive or soft-deleted dependents.

Use the inspected test helper or the [query observer](01-row-lock-observation.md); it must be registered before invoking the action.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ServicePlans\DeactivateServicePlan;
use App\Exceptions\CannotDeactivateServicePlan;
use App\Models\Cabinet;
use App\Models\ServicePlan;

it('rejects parent records with active assignments', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();
    Cabinet::factory()
        ->recycle([$servicePlan, $servicePlan->team])
        ->createOne();

    expect(fn () => resolve(DeactivateServicePlan::class)->handle($servicePlan))
        ->toThrow(
            CannotDeactivateServicePlan::class,
            'Cannot deactivate a service plan with active cabinets.'
        );

    assertDatabaseHas(ServicePlan::class, [
        'id' => $servicePlan->id,
        'deactivated_at' => null,
    ]);
});

it('observes a row-lock query', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    assertRowLockQueryObserved($servicePlan);

    resolve(DeactivateServicePlan::class)->handle($servicePlan);
});

it('deactivates a record', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    resolve(DeactivateServicePlan::class)->handle($servicePlan);

    assertDatabaseHas(ServicePlan::class, [
        'id' => $servicePlan->id,
        'deactivated_at' => now(),
    ]);
});

it('deactivates when related assignments are inactive', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    Cabinet::factory()
        ->deactivated()
        ->recycle([$servicePlan, $servicePlan->team])
        ->createOne();

    resolve(DeactivateServicePlan::class)->handle($servicePlan);

    assertDatabaseHas(ServicePlan::class, [
        'id' => $servicePlan->id,
        'deactivated_at' => now(),
    ]);
});

it('deactivates when related assignments are soft deleted', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    Cabinet::factory()
        ->trashed()
        ->recycle([$servicePlan, $servicePlan->team])
        ->createOne();

    resolve(DeactivateServicePlan::class)->handle($servicePlan);

    assertDatabaseHas(ServicePlan::class, [
        'id' => $servicePlan->id,
        'deactivated_at' => now(),
    ]);
});
```
