# Destroy Tests: Policy Access Restrictions

HTTP 403 for DELETE destroy with an inactive target, inactive route parent or ancestor, or an already active reactivation target. Real lifecycle fixtures stop before the action; action-thrown validation errors are a separate contract.

Place after 404 cases and before action errors. Apply only guards present in the live policy; some reactivation endpoints are idempotent. Keep explicit no-action expectations; a 403-only variant does not separately assert delegation.

## Inactive Target

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\ServicePlans\DeleteServicePlan;
use App\Models\ServicePlan;

describe('destroy', function (): void {
    it('prevents deleting when the record is inactive', function (): void {
        $servicePlan = ServicePlan::factory()->deactivated()->createOne();

        signIn(team: $servicePlan->team);

        mock(DeleteServicePlan::class)
            ->shouldNotReceive('handle');

        $response = delete(route('teams.service-plans.destroy', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]));

        $response->assertForbidden();
    });
});
```

## Inactive Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\ServicePlans\DeletePlanRule;
use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('destroy', function (): void {
    it('prevents deleting when the parent is inactive', function (): void {
        $planRule = PlanRule::factory()
            ->for(ServicePlan::factory()->deactivated())
            ->createOne();

        signIn(team: $planRule->servicePlan->team);

        mock(DeletePlanRule::class)
            ->shouldNotReceive('handle');

        $response = delete(route('teams.service-plans.plan-rules.destroy', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertForbidden();
    });
});
```

## Inactive Ancestor

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;

use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('destroy', function (): void {
    it('prevents deleting when the ancestor is inactive', function (): void {
        $planRule = PlanRule::factory()
            ->for(ServicePlan::factory()->deactivated())
            ->createOne();
        $rate = PlanRate::factory()
            ->for($planRule, 'planRule')
            ->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        $response = delete(route('teams.service-plans.plan-rules.rates.destroy', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertForbidden();
    });
});
```

## Already Active Reactivation Target

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

use App\Actions\WorkOrderStatuses\ReactivateWorkOrderStatus;
use App\Models\WorkOrderStatus;

describe('destroy', function (): void {
    it('prevents reactivating when the record is active', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        signIn(team: $workOrderStatus->team);

        mock(ReactivateWorkOrderStatus::class)
            ->shouldNotReceive('handle');

        $response = delete(route('teams.work-order-statuses.deactivation.destroy', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]));

        $response->assertForbidden();
    });
});
```
