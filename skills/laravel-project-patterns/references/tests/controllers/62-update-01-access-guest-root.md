# Update Tests: Access Guest Root

Pest PATCH update: Browser guests on tenant settings and direct-record routes, with empty requests or valid name and note payloads. Preserve each required route parameter.

## Requires authentication — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Team;

describe('update', function (): void {
    it('requires authentication', function (): void {
        $team = Team::factory()->createOne();

        $response = patch(route('teams.update', [
            'team' => $team,
        ]));

        $response->assertRedirectToRoute('login');
    });
});
```

## Requires authentication — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\Member;

describe('update', function (): void {
    it('requires authentication', function (): void {
        $member = Member::factory()->createOne();

        $response = patch(route('teams.members.update', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertRedirectToRoute('login');
    });
});
```

## Requires authentication — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\WorkOrder;

describe('update', function (): void {
    it('requires authentication', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectToRoute('login');
    });
});
```

## Requires authentication — variant 4

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\ItemGroup;

describe('update', function (): void {
    it('requires authentication', function (): void {
        $itemGroup = ItemGroup::factory()->createOne();

        $response = patch(route('teams.item-groups.update', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]), [
            'name' => 'Computers',
        ]);

        $response->assertRedirectToRoute('login');
    });
});
```
