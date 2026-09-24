# Update Tests: Bindings Foreign Parent

PATCH update: One-parent routes with a foreign parent return 404: aligned foreign chains and parent-only replacement; fixtures do not isolate every edge.

## Returns not found when the parent belongs to another tenant — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\MemberAddress;
use App\Models\Team;

describe('update', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $address = MemberAddress::factory()->createOne();
        $team = Team::factory()->createOne();

        login(team: $team);

        $response = patch(route('teams.members.addresses.update', [
            'team' => $team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertNotFound();
    });
});
```

## Returns not found when the parent belongs to another tenant — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Cabinet;
use App\Models\Team;

describe('update', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $cabinet = Cabinet::factory()->createOne();

        login(team: $team);

        $response = patch(route('teams.members.cabinets.update', [
            'team' => $team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });
});
```

## Returns not found when the parent belongs to another tenant — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Team;
use App\Models\WorkOrderLine;

describe('update', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $line = WorkOrderLine::factory()->createOne();

        login(team: $team);

        $response = patch(route('teams.work-orders.lines.update', [
            'team' => $team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]), ['description' => 'Updated']);

        $response->assertNotFound();
    });
});
```

## Returns not found when the parent belongs to another tenant — variant 4

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('update', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $planRule = PlanRule::factory()->createOne();
        $unrelatedServicePlan = ServicePlan::factory()->createOne();

        login(team: $planRule->servicePlan->team);

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $unrelatedServicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertNotFound();
    });
});
```
