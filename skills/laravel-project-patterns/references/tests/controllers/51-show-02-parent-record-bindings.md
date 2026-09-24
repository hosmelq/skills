# Show Tests: Record Binding Under One Parent

GET show tenant / parent / record binding: a target under another same-tenant parent, from another tenant or soft deleted returns 404. Alternative fixture graphs retain independently owned rows and parent-scoped uniqueness.

## Member Children

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use App\Models\MemberAddress;

describe('show', function (): void {
    it('returns not found when the record belongs to another parent in the same tenant', function (): void {
        $member = Member::factory()->createOne();
        $unrelatedAddress = MemberAddress::factory()->recycle($member->team)->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.addresses.show', [
            'team' => $member->team,
            'member' => $member,
            'address' => $unrelatedAddress,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record belongs to another tenant', function (): void {
        $member = Member::factory()->createOne();
        $address = MemberAddress::factory()->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.addresses.show', [
            'team' => $member->team,
            'member' => $member,
            'address' => $address,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $address = MemberAddress::factory()->trashed()->createOne();

        login(team: $address->member->team);

        $response = get(route('teams.members.addresses.show', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertNotFound();
    });
});
```

## Independently Owned Child

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

describe('show', function (): void {
    it('returns not found when the record belongs to another parent in the same tenant', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()->recycle($team)->createOne();
        $otherLine = WorkOrderLine::factory()->recycle($team)->createOne();

        login(team: $team);

        $response = get(route('teams.work-orders.lines.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
            'line' => $otherLine,
        ]));

        $response->assertNotFound();
    });
});
```

## Plan Child

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('show', function (): void {
    it('returns not found when the record belongs to another parent in the same tenant', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();
        $unrelatedPlanRule = PlanRule::factory()->recycle($servicePlan->team)->createOne();

        login(team: $servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.show', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
            'plan_rule' => $unrelatedPlanRule,
        ]));

        $response->assertNotFound();
    });
});
```
