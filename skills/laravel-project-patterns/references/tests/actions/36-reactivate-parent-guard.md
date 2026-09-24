# Action Tests: Reactivate Under an Active Parent

Integration tests for reactivation with a parent precondition: reject an inactive parent while preserving the original timestamp, then clear the timestamp for an eligible record.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Cabinets\ReactivateCabinet;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\Cabinet;
use App\Models\ServicePlan;

it('rejects assignments with a deactivated parent record', function (): void {
    $cabinet = Cabinet::factory()
        ->deactivated()
        ->for(ServicePlan::factory()->deactivated(), 'servicePlan')
        ->createOne();
    $deactivatedAt = $cabinet->deactivated_at;

    expect(fn () => resolve(ReactivateCabinet::class)->handle($cabinet))
        ->toThrow(
            CannotUseDeactivatedServicePlan::class,
            'Cannot use a deactivated service plan.',
        );

    assertDatabaseHas(Cabinet::class, [
        'id' => $cabinet->id,
        'deactivated_at' => $deactivatedAt,
    ]);
});

it('reactivates a record', function (): void {
    $cabinet = Cabinet::factory()->deactivated()->createOne();

    resolve(ReactivateCabinet::class)->handle($cabinet);

    assertDatabaseHas(Cabinet::class, [
        'id' => $cabinet->id,
        'deactivated_at' => null,
    ]);
});
```
