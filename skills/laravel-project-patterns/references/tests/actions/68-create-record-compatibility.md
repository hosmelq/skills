# Action Tests: Create Related Selection Guards

Integration action tests: Require the selected rule to belong to the selected plan, reject a rule without its plan, and reject a mismatched weight unit.

```php
<?php

declare(strict_types=1);

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Enums\WeightUnit;
use App\Exceptions\WorkOrders\PlanRuleDoesNotBelongToServicePlan;
use App\Exceptions\WorkOrders\PlanRuleRequiresServicePlan;
use App\Exceptions\WorkOrders\WeightUnitDoesNotMatchServicePlan;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrderStatus;

it('rejects a rule from another plan', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $planRule = PlanRule::factory()->createOne();

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'plan_rule_id' => $planRule->id,
        'service_plan_id' => $servicePlan->id,
    ])))->toThrow(
        PlanRuleDoesNotBelongToServicePlan::class,
        'The selected plan rule does not belong to the selected service plan.',
    );
});

it('rejects a rule without a plan', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $planRule = PlanRule::factory()->createOne();

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'plan_rule_id' => $planRule->id,
    ])))->toThrow(
        PlanRuleRequiresServicePlan::class,
        'A service plan is required when selecting a plan rule.',
    );
});

it('rejects a mismatched weight unit', function (): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne([
        'weight_unit' => WeightUnit::Pounds,
    ]);

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'service_plan_id' => $servicePlan->id,
        'weight' => '1.0000',
        'weight_unit' => WeightUnit::Kilograms->value,
    ])))->toThrow(
        WeightUnitDoesNotMatchServicePlan::class,
        'The work order weight unit does not match the selected service plan.',
    );
});
```
