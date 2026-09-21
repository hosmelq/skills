# Update Tests: Access Guest Nested

Pest PATCH update: Browser PATCH guests on one-parent and two-parent record routes; preserve the valid description payload variant.

## Requires authentication — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\MemberAddress;

describe('update', function (): void {
    it('requires authentication', function (): void {
        $address = MemberAddress::factory()->createOne();

        $response = patch(route('teams.members.addresses.update', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertRedirectToRoute('login');
    });
});
```

## Requires authentication — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\WorkOrderLine;

describe('update', function (): void {
    it('requires authentication', function (): void {
        $line = WorkOrderLine::factory()->createOne();

        $response = patch(
            route('teams.work-orders.lines.update', [
                'team' => $line->workOrder->team,
                'work_order' => $line->workOrder,
                'line' => $line,
            ]),
            ['description' => 'Updated'],
        );

        $response->assertRedirectToRoute('login');
    });
});
```

## Requires authentication — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\PlanRate;

describe('update', function (): void {
    it('requires authentication', function (): void {
        $rate = PlanRate::factory()->createOne();

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertRedirectToRoute('login');
    });
});
```
