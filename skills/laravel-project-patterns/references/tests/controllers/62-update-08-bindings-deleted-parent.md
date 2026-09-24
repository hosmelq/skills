# Update Tests: Bindings Deleted Parent

PATCH update: A live child under a deleted parent; preserve relation factories, explicit inherited tenant IDs and the supplied-description request.

## Returns not found when the parent is soft deleted — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Member;
use App\Models\MemberAddress;

describe('update', function (): void {
    it('returns not found when the parent is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();
        $address = MemberAddress::factory()->recycle($member)->createOne();

        login(team: $member->team);

        $response = patch(route('teams.members.addresses.update', [
            'team' => $member->team,
            'member' => $member,
            'address' => $address,
        ]));

        $response->assertNotFound();
    });
});
```

## Returns not found when the parent is soft deleted — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Cabinet;
use App\Models\Member;

describe('update', function (): void {
    it('returns not found when the parent is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();
        $cabinet = Cabinet::factory()->createOne([
            'member_id' => $member->id,
            'team_id' => $member->team_id,
        ]);

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

## Returns not found when the parent is soft deleted — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

describe('update', function (): void {
    it('returns not found when the parent is soft deleted', function (): void {
        $workOrder = WorkOrder::factory()->trashed()->createOne();
        $line = WorkOrderLine::factory()->recycle($workOrder)->createOne();

        login(team: $workOrder->team);

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
