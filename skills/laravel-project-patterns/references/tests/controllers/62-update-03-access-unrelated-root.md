# Update Tests: Access Unrelated Root

Pest PATCH update: Unrelated-tenant denials cover settings membership and direct-record routes with empty or valid supplied payloads.

## Prevents updating from an unrelated tenant — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Team;

describe('update', function (): void {
    it('prevents updating from an unrelated tenant', function (): void {
        $unrelatedTeam = Team::factory()->createOne();

        $userTeam = Team::factory()->createOne();

        signIn(team: $userTeam);

        $response = patch(route('teams.update', [
            'team' => $unrelatedTeam,
        ]));

        $response->assertForbidden();
    });
});
```

## Prevents updating from an unrelated tenant — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Member;

describe('update', function (): void {
    it('prevents updating from an unrelated tenant', function (): void {
        $unrelatedMember = Member::factory()->createOne();

        signIn();

        $response = patch(route('teams.members.update', [
            'team' => $unrelatedMember->team,
            'member' => $unrelatedMember,
        ]));

        $response->assertForbidden();
    });
});
```

## Prevents updating from an unrelated tenant — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\WorkOrder;

describe('update', function (): void {
    it('prevents updating from an unrelated tenant', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn();

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertForbidden();
    });
});
```

## Prevents updating from an unrelated tenant — variant 4

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\ItemGroup;

describe('update', function (): void {
    it('prevents updating from an unrelated tenant', function (): void {
        $workOrderItemGroup = ItemGroup::factory()->createOne();

        signIn();

        $response = patch(route('teams.item-groups.update', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
        ]), [
            'name' => 'Computers',
        ]);

        $response->assertForbidden();
    });
});
```

## Prevents updating from an unrelated tenant — variant 5

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\WorkOrderStatus;

describe('update', function (): void {
    it('prevents updating from an unrelated tenant', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        signIn();

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'name' => 'Received',
        ]);

        $response->assertForbidden();
    });
});
```
