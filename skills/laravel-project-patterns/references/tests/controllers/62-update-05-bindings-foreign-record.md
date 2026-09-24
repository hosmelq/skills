# Update Tests: Bindings Foreign Record

Pest PATCH update: A direct record from another tenant returns 404. Preserve the authorized URL tenant, foreign record fixture and supplied request payload.

## Returns not found when the record belongs to another tenant — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Member;
use App\Models\Team;

describe('update', function (): void {
    it('returns not found when the record belongs to another tenant', function (): void {
        $relatedTeam = Team::factory()->createOne();

        $unrelatedMember = Member::factory()->createOne();

        signIn(team: $relatedTeam);

        $response = patch(route('teams.members.update', [
            'team' => $relatedTeam,
            'member' => $unrelatedMember,
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

use App\Models\Team;
use App\Models\WorkOrder;

describe('update', function (): void {
    it('returns not found when the record belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $team);

        $response = patch(route('teams.work-orders.update', [
            'team' => $team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertNotFound();
    });
});
```

## Returns not found when the record belongs to another tenant — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\ItemGroup;
use App\Models\Team;

describe('update', function (): void {
    it('returns not found when the record belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $itemGroup = ItemGroup::factory()->createOne();

        signIn(team: $team);

        $response = patch(route('teams.item-groups.update', [
            'team' => $team,
            'item_group' => $itemGroup,
        ]), [
            'name' => 'Computers',
        ]);

        $response->assertNotFound();
    });
});
```

## Returns not found when the record belongs to another tenant — variant 4

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Team;
use App\Models\WorkOrderStatus;

describe('update', function (): void {
    it('returns not found when the record belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();

        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        signIn(team: $team);

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'name' => 'Received',
        ]);

        $response->assertNotFound();
    });
});
```
