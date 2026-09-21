# Update Tests: Bindings Wrong Parent

Pest PATCH update: Same-tenant wrong-parent fixtures retain recycled tenant, explicit independent parent creation and child ownership graphs.

## Returns not found when the record belongs to another parent in the same tenant — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Member;
use App\Models\MemberAddress;

describe('update', function (): void {
    it('returns not found when the record belongs to another parent in the same tenant', function (): void {
        $member = Member::factory()->createOne();

        $unrelatedAddress = MemberAddress::factory()
            ->for(Member::factory()->recycle($member->team)->createOne())
            ->createOne();

        signIn(team: $member->team);

        $response = patch(route('teams.members.addresses.update', [
            'team' => $member->team,
            'member' => $member,
            'address' => $unrelatedAddress,
        ]));

        $response->assertNotFound();
    });
});
```

## Returns not found when the record belongs to another parent in the same tenant — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Cabinet;
use App\Models\Member;

describe('update', function (): void {
    it('returns not found when the record belongs to another parent in the same tenant', function (): void {
        $member = Member::factory()->createOne();
        $otherMember = Member::factory()->for($member->team)->createOne();
        $cabinet = Cabinet::factory()
            ->for($otherMember)
            ->createOne();

        signIn(team: $member->team);

        $response = patch(route('teams.members.cabinets.update', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });
});
```

## Returns not found when the record belongs to another parent in the same tenant — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

describe('update', function (): void {
    it('returns not found when the record belongs to another parent in the same tenant', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()->for($team)->createOne();
        $otherWorkOrder = WorkOrder::factory()->for($team)->createOne();
        $otherItem = WorkOrderLine::factory()->for($otherWorkOrder)->createOne();

        signIn(team: $team);

        $response = patch(
            route('teams.work-orders.lines.update', [
                'team' => $workOrder->team,
                'work_order' => $workOrder,
                'line' => $otherItem,
            ]),
            ['description' => 'Updated'],
        );

        $response->assertNotFound();
    });
});
```

## Returns not found when the record belongs to another parent in the same tenant — variant 4

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('update', function (): void {
    it('returns not found when the record belongs to another parent in the same tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();

        $unrelatedPlanRule = PlanRule::factory()
            ->recycle($servicePlan->team)
            ->createOne();

        signIn(team: $servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $unrelatedPlanRule,
        ]));

        $response->assertNotFound();
    });
});
```
