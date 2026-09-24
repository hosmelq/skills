# Store Tests: Access Inactive State

POST store: Inactive parent or ancestor prevents creation; already inactive record prevents deactivation. Preserve negative mock assertions and supplied payload.

## Prevents deactivating when the record is inactive — response only

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\ItemGroup;

describe('store', function (): void {
    it('prevents deactivating when the record is inactive', function (): void {
        $itemGroup = ItemGroup::factory()->deactivated()->createOne();

        login(team: $itemGroup->team);

        $response = post(route('teams.item-groups.deactivation.store', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]));

        $response->assertForbidden();
    });
});
```

## Prevents deactivating when the record is inactive — action not called

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrderStatuses\DeactivateWorkOrderStatus;
use App\Models\WorkOrderStatus;

describe('store', function (): void {
    it('prevents deactivating when the record is inactive', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->deactivated()->createOne();

        login(team: $workOrderStatus->team);

        mock(DeactivateWorkOrderStatus::class)
            ->shouldNotReceive('handle');

        $response = post(route('teams.work-order-statuses.deactivation.store', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]));

        $response->assertForbidden();
    });
});
```
## Prevents storing when the parent is inactive

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ServicePlans\CreatePlanRule;
use App\Models\ServicePlan;

describe('store', function (): void {
    it('prevents storing when the parent is inactive', function (): void {
        $servicePlan = ServicePlan::factory()->deactivated()->createOne();

        login(team: $servicePlan->team);

        mock(CreatePlanRule::class)
            ->shouldNotReceive('handle');

        $response = post(route('teams.service-plans.plan-rules.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]));

        $response->assertForbidden();
    });
});
```

## Prevents storing when the ancestor is inactive

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ServicePlans\CreatePlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('store', function (): void {
    it('prevents storing when the ancestor is inactive', function (): void {
        $planRule = PlanRule::factory()->for(ServicePlan::factory()->deactivated())->createOne();

        login(team: $planRule->servicePlan->team);

        mock(CreatePlanRate::class)
            ->shouldNotReceive('handle');

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]), [
            'maximum_weight' => '5',
            'minimum_weight' => '0',
            'name' => '0 to 5',
            'rate' => '2.50',
        ]);

        $response->assertForbidden();
    });
});
```
