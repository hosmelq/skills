# Action Tests: Delete Under an Active Parent

Integration tests for child deletion: reject an inactive direct parent and keep the child unchanged; soft delete the child when the parent is active.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

use App\Actions\ServicePlans\DeletePlanRule;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRule;
use App\Models\ServicePlan;

it('rejects deactivated parent records', function (): void {
    $planRule = PlanRule::factory()
        ->for(ServicePlan::factory()->deactivated())
        ->createOne();

    expect(fn () => resolve(DeletePlanRule::class)->handle($planRule))->toThrow(
        CannotUseDeactivatedServicePlan::class,
        'Cannot use a deactivated service plan.',
    );

    assertNotSoftDeleted($planRule);
});

it('soft deletes a record under an active parent', function (): void {
    $planRule = PlanRule::factory()->createOne();

    resolve(DeletePlanRule::class)->handle($planRule);

    assertSoftDeleted($planRule);
});
```
