# Update Tests: Bindings Nested Record

Pest PATCH update: Foreign or deleted nested leaf records under the current route parent; keep normal foreign chains distinct from direct-owner conflicts.

## Complete block — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Member;
use App\Models\MemberAddress;

describe('update', function (): void {
    it('returns not found when the record belongs to another tenant', function (): void {
        $member = Member::factory()->createOne();
        $address = MemberAddress::factory()->createOne();

        signIn(team: $member->team);

        $response = patch(route('teams.members.addresses.update', [
            'team' => $member->team,
            'member' => $member,
            'address' => $address,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $address = MemberAddress::factory()->trashed()->createOne();

        signIn(team: $address->member->team);

        $response = patch(route('teams.members.addresses.update', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertNotFound();
    });
});
```

## Returns not found when the record belongs to another tenant — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\PlanRule;

describe('update', function (): void {
    it('returns not found when the record belongs to another tenant', function (): void {
        $planRule = PlanRule::factory()->createOne();

        $unrelatedPlanRule = PlanRule::factory()->createOne();

        signIn(team: $planRule->servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $unrelatedPlanRule,
        ]));

        $response->assertNotFound();
    });
});
```

## Returns not found when the record is soft deleted — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

describe('update', function (): void {
    it('returns not found when the record is soft deleted', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $line = WorkOrderLine::factory()->trashed()->for($workOrder)->createOne();

        signIn(team: $workOrder->team);

        $response = patch(
            route('teams.work-orders.lines.update', [
                'team' => $workOrder->team,
                'work_order' => $workOrder,
                'line' => $line,
            ]),
            ['description' => 'Updated'],
        );

        $response->assertNotFound();
    });
});
```
