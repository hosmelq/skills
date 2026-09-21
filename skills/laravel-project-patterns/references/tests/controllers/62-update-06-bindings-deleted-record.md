# Update Tests: Bindings Deleted Record

Pest PATCH update: A soft deleted direct record returns 404. Each record fixture supplies its own matching URL tenant; preserve the tested request payload.

## Returns not found when the record is soft deleted — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Member;

describe('update', function (): void {
    it('returns not found when the record is soft deleted', function (): void {
        $member = Member::factory()->trashed()->createOne();

        signIn(team: $member->team);

        $response = patch(route('teams.members.update', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertNotFound();
    });
});
```

## Returns not found when the record is soft deleted — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\WorkOrder;

describe('update', function (): void {
    it('returns not found when the record is soft deleted', function (): void {
        $workOrder = WorkOrder::factory()->trashed()->createOne();

        signIn(team: $workOrder->team);

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertNotFound();
    });
});
```

## Returns not found when the record is soft deleted — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\ItemGroup;

describe('update', function (): void {
    it('returns not found when the record is soft deleted', function (): void {
        $workOrderItemGroup = ItemGroup::factory()->trashed()->createOne();

        signIn(team: $workOrderItemGroup->team);

        $response = patch(route('teams.item-groups.update', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
        ]), [
            'name' => 'Computers',
        ]);

        $response->assertNotFound();
    });
});
```

## Returns not found when the record is soft deleted — variant 4

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\WorkOrderStatus;

describe('update', function (): void {
    it('returns not found when the record is soft deleted', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->trashed()->createOne();

        signIn(team: $workOrderStatus->team);

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'name' => 'Received',
        ]);

        $response->assertNotFound();
    });
});
```
