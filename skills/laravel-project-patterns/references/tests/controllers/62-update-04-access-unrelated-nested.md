# Update Tests: Access Unrelated Nested

Pest PATCH update: Unrelated-tenant PATCH denials on valid one-parent and two-parent routes, including the supplied-description variant.

## Prevents updating from an unrelated tenant — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\MemberAddress;

describe('update', function (): void {
    it('prevents updating from an unrelated tenant', function (): void {
        $address = MemberAddress::factory()->createOne();

        login();

        $response = patch(route('teams.members.addresses.update', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertForbidden();
    });
});
```

## Prevents updating from an unrelated tenant — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\WorkOrderLine;

describe('update', function (): void {
    it('prevents updating from an unrelated tenant', function (): void {
        $line = WorkOrderLine::factory()->createOne();

        login();

        $response = patch(
            route('teams.work-orders.lines.update', [
                'team' => $line->workOrder->team,
                'work_order' => $line->workOrder,
                'line' => $line,
            ]),
            ['description' => 'Updated'],
        );

        $response->assertForbidden();
    });
});
```

## Prevents updating from an unrelated tenant — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\PlanRate;

describe('update', function (): void {
    it('prevents updating from an unrelated tenant', function (): void {
        $rate = PlanRate::factory()->createOne();

        login();

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertForbidden();
    });
});
```
