# Store Tests: Access Guest Nested

Pest POST store: Web guest creating under a parent or ancestor chain; distinguish empty request from valid description/quantity payload.

## Requires authentication — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Member;

describe('store', function (): void {
    it('requires authentication', function (): void {
        $member = Member::factory()->createOne();

        $response = post(route('teams.members.addresses.store', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertRedirectToRoute('login');
    });
});
```

## Requires authentication — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\WorkOrder;

describe('store', function (): void {
    it('requires authentication', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'description' => 'Laptop computer',
            'quantity' => 2,
        ]);

        $response->assertRedirectToRoute('login');
    });
});
```

## Requires authentication — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\PlanRule;

describe('store', function (): void {
    it('requires authentication', function (): void {
        $planRule = PlanRule::factory()->createOne();

        $response = post(route('teams.service-plans.plan-rules.rates.store', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertRedirectToRoute('login');
    });
});
```
