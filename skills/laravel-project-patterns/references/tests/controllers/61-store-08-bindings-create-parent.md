# Store Tests: Bindings Create Parent

POST store: Creation parent belongs to another tenant or is soft deleted; empty request versus valid description/quantity request.

## Empty request payload

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Member;
use App\Models\Team;

describe('store', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $relatedTeam = Team::factory()->createOne();
        $unrelatedMember = Member::factory()->createOne();

        login(team: $relatedTeam);

        $response = post(route('teams.members.addresses.store', [
            'team' => $relatedTeam,
            'member' => $unrelatedMember,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();

        login(team: $member->team);

        $response = post(route('teams.members.addresses.store', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertNotFound();
    });
});
```

## Required description and quantity payload

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Team;
use App\Models\WorkOrder;

describe('store', function (): void {
    it('returns not found when the parent belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $team,
            'work_order' => $workOrder,
        ]), [
            'description' => 'Laptop computer',
            'quantity' => 2,
        ]);

        $response->assertNotFound();
    });

    it('returns not found when the parent is soft deleted', function (): void {
        $workOrder = WorkOrder::factory()->trashed()->createOne();

        login(team: $workOrder->team);

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'description' => 'Laptop computer',
            'quantity' => 2,
        ]);

        $response->assertNotFound();
    });
});
```
