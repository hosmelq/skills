# Action Tests: Delete Under an Active Ancestor

Integration tests for grandchild deletion: reject an inactive ancestor through an intermediate relation and keep the target unchanged; soft delete it when the ancestor is active.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

use App\Actions\ServicePlans\DeletePlanRate;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;

it('rejects a deactivated ancestor', function (): void {
    $planRule = PlanRule::factory()
        ->for(ServicePlan::factory()->deactivated())
        ->createOne();
    $rate = PlanRate::factory()
        ->recycle($planRule)
        ->createOne();

    expect(fn () => resolve(DeletePlanRate::class)->handle($rate))->toThrow(
        CannotUseDeactivatedServicePlan::class,
        'Cannot use a deactivated service plan.',
    );

    assertNotSoftDeleted($rate);
});

it('soft deletes a record under an active ancestor', function (): void {
    $rate = PlanRate::factory()->createOne();

    resolve(DeletePlanRate::class)->handle($rate);

    assertSoftDeleted($rate);
});
```
