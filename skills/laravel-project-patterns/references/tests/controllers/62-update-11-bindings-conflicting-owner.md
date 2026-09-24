# Update Tests: Bindings Conflicting Owner

PATCH update: The leaf belongs to the correct parent but its directly stored tenant differs; both cabinet and line fixture graphs are retained.

## Returns not found when the record tenant does not match its parent tenant — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\Team;

describe('update', function (): void {
    it('returns not found when the record tenant does not match its parent tenant', function (): void {
        $member = Member::factory()->createOne();
        $otherTeam = Team::factory()->createOne();
        $cabinet = Cabinet::factory()
            ->for($member)
            ->for($otherTeam)
            ->createOne();

        login(team: $member->team);

        $response = patch(route('teams.members.cabinets.update', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });
});
```

## Returns not found when the record tenant does not match its parent tenant — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

describe('update', function (): void {
    it('returns not found when the record tenant does not match its parent tenant', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $otherTeam = Team::factory()->createOne();
        $line = WorkOrderLine::factory()->for($workOrder)->for($otherTeam)->createOne();

        login(team: $workOrder->team);

        $response = patch(route('teams.work-orders.lines.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
            'line' => $line,
        ]), ['description' => 'Updated']);

        $response->assertNotFound();
    });
});
```
