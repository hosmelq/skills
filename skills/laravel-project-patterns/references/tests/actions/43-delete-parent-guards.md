# Action Tests: Delete Parent Dependency Guards

Integration tests for parent deletion with several dependency types: retain live and soft-deleted child rules, assignments and history as blockers; observe row-lock SQL; soft delete a parent without dependencies.

Use the inspected test helper or the [query observer](01-row-lock-observation.md); it must be registered before invoking the action.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;

use App\Actions\ServicePlans\DeleteServicePlan;
use App\Exceptions\CannotDeleteServicePlanInUse;
use App\Models\Cabinet;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\WorkOrder;

it('rejects parent records with rules', function (): void {
    $planRule = PlanRule::factory()->createOne();

    expect(fn () => resolve(DeleteServicePlan::class)->handle($planRule->servicePlan))
        ->toThrow(
            CannotDeleteServicePlanInUse::class,
            'Cannot delete a service plan with work orders, plan rules, or cabinets.'
        );

    assertNotSoftDeleted($planRule->servicePlan);
});

it('rejects parent records with soft deleted rules', function (): void {
    $planRule = PlanRule::factory()->trashed()->createOne();

    expect(fn () => resolve(DeleteServicePlan::class)->handle($planRule->servicePlan))
        ->toThrow(
            CannotDeleteServicePlanInUse::class,
            'Cannot delete a service plan with work orders, plan rules, or cabinets.'
        );

    assertNotSoftDeleted($planRule->servicePlan);
});

it('rejects parent records with assignments', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();
    $member = Member::factory()
        ->recycle($servicePlan->team)
        ->createOne();
    $cabinet = Cabinet::factory()
        ->recycle($member)
        ->recycle($servicePlan)
        ->createOne();

    expect(fn () => resolve(DeleteServicePlan::class)->handle($servicePlan))
        ->toThrow(
            CannotDeleteServicePlanInUse::class,
            'Cannot delete a service plan with work orders, plan rules, or cabinets.'
        );

    assertNotSoftDeleted($servicePlan);
    assertNotSoftDeleted($cabinet);
});

it('rejects deleting a parent record referenced by an active related record', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();
    WorkOrder::factory()
        ->for($servicePlan)
        ->recycle($servicePlan->team)
        ->createOne();

    expect(fn () => resolve(DeleteServicePlan::class)->handle($servicePlan))
        ->toThrow(
            CannotDeleteServicePlanInUse::class,
            'Cannot delete a service plan with work orders, plan rules, or cabinets.',
        );

    assertNotSoftDeleted($servicePlan);
});

it('rejects parent records with soft deleted assignments', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();
    $member = Member::factory()
        ->recycle($servicePlan->team)
        ->createOne();
    $cabinet = Cabinet::factory()
        ->trashed()
        ->recycle($member)
        ->recycle($servicePlan)
        ->createOne();

    expect(fn () => resolve(DeleteServicePlan::class)->handle($servicePlan))
        ->toThrow(
            CannotDeleteServicePlanInUse::class,
            'Cannot delete a service plan with work orders, plan rules, or cabinets.'
        );

    assertNotSoftDeleted($servicePlan);
});

it('rejects deleting a parent record referenced by a soft deleted related record', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();
    WorkOrder::factory()
        ->trashed()
        ->for($servicePlan)
        ->recycle($servicePlan->team)
        ->createOne();

    expect(fn () => resolve(DeleteServicePlan::class)->handle($servicePlan))
        ->toThrow(
            CannotDeleteServicePlanInUse::class,
            'Cannot delete a service plan with work orders, plan rules, or cabinets.',
        );

    assertNotSoftDeleted($servicePlan);
});

it('observes a row-lock query', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    assertRowLockQueryObserved($servicePlan);

    resolve(DeleteServicePlan::class)->handle($servicePlan);
});

it('deletes a parent record without dependencies', function (): void {
    $servicePlan = ServicePlan::factory()->createOne();

    resolve(DeleteServicePlan::class)->handle($servicePlan);

    assertSoftDeleted($servicePlan);
});
```
