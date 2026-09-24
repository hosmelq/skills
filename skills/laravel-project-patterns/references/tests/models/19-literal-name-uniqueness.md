# Model Tests: Same-Value Name Uniqueness

Database uniqueness for an identical active name within a tenant, including deactivated reservation and reuse after explicit deletion. This example does not establish case-insensitive comparison.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\ServicePlan;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces active name uniqueness per tenant at the database level', function (): void {
    $servicePlan = ServicePlan::factory()->createOne([
        'name' => 'Standard Service',
    ]);

    expect(fn () => ServicePlan::factory()->recycle($servicePlan->team)->createOne([
        'name' => 'Standard Service',
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('service_plans_active_name_unique');
    });
});

it('keeps deactivated names reserved at the database level', function (): void {
    $servicePlan = ServicePlan::factory()->deactivated()->createOne([
        'name' => 'Standard Service',
    ]);

    expect(fn () => ServicePlan::factory()->recycle($servicePlan->team)->createOne([
        'name' => 'Standard Service',
    ]))->toThrow(function (UniqueConstraintViolationException $exception): void {
        expect($exception->index)->toBe('service_plans_active_name_unique');
    });
});

it('allows reusing names after soft deletion', function (): void {
    $servicePlan = ServicePlan::factory()->createOne([
        'name' => 'Standard Service',
    ]);

    $servicePlan->delete();

    $replacement = ServicePlan::factory()->recycle($servicePlan->team)->createOne([
        'name' => 'Standard Service',
    ]);

    assertDatabaseHas(ServicePlan::class, [
        'id' => $replacement->id,
        'name' => 'Standard Service',
    ]);
});
```
