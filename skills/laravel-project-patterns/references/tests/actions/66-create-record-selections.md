# Action Tests: Create Assignment and Rule Guards

Integration action tests: Reject unavailable assignments and plans across tenant, active and soft-delete states, and a trashed selected rule.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseCount;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Exceptions\WorkOrders\CabinetIsUnavailable;
use App\Exceptions\WorkOrders\PlanRuleDoesNotBelongToServicePlan;
use App\Exceptions\WorkOrders\ServicePlanIsUnavailable;
use App\Models\Cabinet;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;

it('rejects an unavailable assignment', function (string $state): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $cabinet = match ($state) {
        'another team' => Cabinet::factory()->createOne(),
        'deactivated' => Cabinet::factory()->deactivated()->recycle($team)->createOne(),
        'soft deleted' => Cabinet::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'cabinet_id' => $cabinet->id,
    ])))->toThrow(
        CabinetIsUnavailable::class,
        'The selected cabinet is unavailable.',
    );

    assertDatabaseCount(WorkOrder::class, 0);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);

it('rejects an unavailable plan', function (string $state): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $servicePlan = match ($state) {
        'another team' => ServicePlan::factory()->createOne(),
        'deactivated' => ServicePlan::factory()->deactivated()->recycle($team)->createOne(),
        'soft deleted' => ServicePlan::factory()->trashed()->recycle($team)->createOne(),
    };

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'service_plan_id' => $servicePlan->id,
    ])))->toThrow(
        ServicePlanIsUnavailable::class,
        'The selected service plan is unavailable.',
    );

    assertDatabaseCount(WorkOrder::class, 0);
})->with([
    'another team',
    'deactivated',
    'soft deleted',
]);

it('rejects an unavailable rule', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $planRule = PlanRule::factory()->trashed()->recycle($servicePlan)->createOne();

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'plan_rule_id' => $planRule->id,
        'service_plan_id' => $servicePlan->id,
    ])))->toThrow(
        PlanRuleDoesNotBelongToServicePlan::class,
        'The selected plan rule does not belong to the selected service plan.',
    );

    assertDatabaseCount(WorkOrder::class, 0);
});
```
