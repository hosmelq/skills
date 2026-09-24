# Store Tests: Access Guest Nested

Pest POST store: Browser guest authentication below a parent or ancestor chain. Preserve every route binding and use the endpoint’s valid payload; both cases redirect to login.

## Parent route binding

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

## Ancestor and parent route bindings

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
