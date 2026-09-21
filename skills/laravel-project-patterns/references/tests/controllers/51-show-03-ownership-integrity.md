# Show Tests: Conflicting Record Ownership

GET show returns 404 when the record has the correct parent foreign key but a conflicting tenant ID. Two complete independently owned child examples preserve this boundary beyond ordinary wrong-parent binding.

## Cabinet Ownership

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\Team;

describe('show', function (): void {
    it('returns not found when the record tenant does not match its parent tenant', function (): void {
        $member = Member::factory()->createOne();
        $otherTeam = Team::factory()->createOne();
        $cabinet = Cabinet::factory()
            ->for($member)
            ->for($otherTeam)
            ->createOne();

        signIn(team: $member->team);

        $response = get(route('teams.members.cabinets.show', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ]));

        $response->assertNotFound();
    });
});
```

## Line Ownership

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

describe('show', function (): void {
    it('returns not found when the record tenant does not match its parent tenant', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $otherTeam = Team::factory()->createOne();
        $line = WorkOrderLine::factory()->for($workOrder)->for($otherTeam)->createOne();

        signIn(team: $workOrder->team);

        $response = get(route('teams.work-orders.lines.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
            'line' => $line,
        ]));

        $response->assertNotFound();
    });
});
```
