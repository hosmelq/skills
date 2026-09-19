# Destroy Tests: Action Lifecycle Errors

DELETE destroy action exceptions for final targets or parents, inactive parents or ancestors, and reactivation with an inactive related record. Use authorized ordinary fixtures, mock the failure and assert exact redirect-back error keys/messages rather than policy 403.

Keep route parents active so policy checks allow the action call. For reactivation only the target is inactive; the mock reports the related failure. A related service plan need not be a URL parent.

## Final Target

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\WorkOrders\DeleteWorkOrder;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\WorkOrder;

describe('destroy', function (): void {
    it('rejects deleting when the record is final', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

        mock(DeleteWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(WorkOrderIsFinal::becauseItCannotBeChangedOrDeleted());

        $response = delete(route('teams.work-orders.destroy', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]));

        $response->assertRedirectBackWithErrors([
            'work_order' => 'Work orders in a final status cannot be changed or deleted.',
        ]);
    });
});
```

## Final Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\WorkOrderLines\DeleteWorkOrderLine;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Models\WorkOrderLine;

describe('destroy', function (): void {
    it('rejects deleting when the parent is final', function (): void {
        $line = WorkOrderLine::factory()->createOne();

        signIn(team: $line->workOrder->team);

        mock(DeleteWorkOrderLine::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(WorkOrderIsFinal::becauseItCannotBeChangedOrDeleted());

        $response = delete(route('teams.work-orders.lines.destroy', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]));

        $response->assertRedirectBackWithErrors([
            'work_order' =>
                'Work order lines cannot be deleted after the work order reaches a final status.',
        ]);
    });
});
```

## Inactive Parent Reported By The Action

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\ServicePlans\DeletePlanRule;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRule;

describe('destroy', function (): void {
    it('rejects deleting when the parent is inactive', function (): void {
        $planRule = PlanRule::factory()->createOne();

        signIn(team: $planRule->servicePlan->team);

        mock(DeletePlanRule::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (PlanRule $planRuleArgument): bool => $planRuleArgument->is($planRule))
            ->andThrow(CannotUseDeactivatedServicePlan::becauseItIsDeactivated());

        $response = delete(route('teams.service-plans.plan-rules.destroy', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan is deactivated.',
        ]);
    });
});
```

## Inactive Ancestor Reported By The Action

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\ServicePlans\DeletePlanRate;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\PlanRate;

describe('destroy', function (): void {
    it('rejects deleting when the ancestor is inactive', function (): void {
        $rate = PlanRate::factory()->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        mock(DeletePlanRate::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                PlanRate $rateArgument
            ): bool => $rateArgument->is($rate))
            ->andThrow(CannotUseDeactivatedServicePlan::becauseItIsDeactivated());

        $response = delete(route('teams.service-plans.plan-rules.rates.destroy', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan is deactivated.',
        ]);
    });
});
```

## Related Record Prevents Reactivation

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\Cabinets\ReactivateCabinet;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Models\Cabinet;

describe('destroy', function (): void {
    it('rejects reactivating when a related record is inactive', function (): void {
        $cabinet = Cabinet::factory()->deactivated()->createOne();

        signIn(team: $cabinet->member->team);

        mock(ReactivateCabinet::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Cabinet $cabinetArgument): bool => $cabinetArgument->is($cabinet))
            ->andThrow(CannotUseDeactivatedServicePlan::becauseItIsDeactivated());

        $response = delete(route('teams.members.cabinets.deactivation.destroy', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertRedirectBackWithErrors([
            'service_plan' => 'This service plan is deactivated.',
        ]);
    });
});
```
