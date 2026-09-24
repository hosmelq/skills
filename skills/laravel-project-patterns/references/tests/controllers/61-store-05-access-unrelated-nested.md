# Store Tests: Access Unrelated Nested

Pest POST store: Unrelated tenant creating under a parent or ancestor; preserve parent fixture vs child-derived parent and valid-payload variants.

## Prevents storing from an unrelated tenant — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Member;

describe('store', function (): void {
    it('prevents storing from an unrelated tenant', function (): void {
        $member = Member::factory()->createOne();

        login();

        $response = post(route('teams.members.addresses.store', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertForbidden();
    });
});
```

## Prevents storing from an unrelated tenant — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\PlanRule;

describe('store', function (): void {
    it('prevents storing from an unrelated tenant', function (): void {
        $unrelatedPlanRule = PlanRule::factory()->createOne();

        login();

        $response = post(route('teams.service-plans.plan-rules.store', [
            'team' => $unrelatedPlanRule->servicePlan->team,
            'service_plan' => $unrelatedPlanRule->servicePlan,
        ]));

        $response->assertForbidden();
    });
});
```

## Prevents storing from an unrelated tenant — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\WorkOrder;

describe('store', function (): void {
    it('prevents storing from an unrelated tenant', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login();

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'description' => 'Laptop computer',
            'quantity' => 2,
        ]);

        $response->assertForbidden();
    });
});
```

## Prevents storing from an unrelated tenant — variant 4

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\PlanRule;

describe('store', function (): void {
    it('prevents storing from an unrelated tenant', function (): void {
        $planRule = PlanRule::factory()->createOne();

        login();

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertForbidden();
    });
});
```
