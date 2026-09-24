# Index Tests: Configured Collection Order

Unpaginated GET index collections use direct array positions. Complete examples cover manual ordering, enum options and a separate exact-count exclusion of foreign and soft deleted records.

Ordering examples prove the stated positions; only the exclusion example asserts the complete collection size.

## Ordered Records

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\ItemGroup;
use App\Models\Team;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('lists records in the configured order', function (): void {
        $team = Team::factory()->createOne();
        $second = ItemGroup::factory()->recycle($team)->createOne([
            'name' => 'Second',
        ]);
        $first = ItemGroup::factory()->recycle($team)->createOne([
            'name' => 'First',
        ]);
        $first->moveOrderUp();

        login(team: $team);

        $response = get(route('teams.item-groups.index', [
            'team' => $team,
        ]));

        $response->assertOk()
            ->assertInertia(function (
                AssertableInertia $page
            ) use ($first, $team, $second): void {
                $page->component('item-groups/Index')
                    ->where('team.id', $team->sqid)
                    ->where('itemGroups.0.id', $first->sqid)
                    ->where('itemGroups.1.id', $second->sqid);
            });
    });
});
```

## Ordered Records With Enum Options

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\WorkOrderBaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('lists records in the configured order', function (): void {
        $team = Team::factory()->createOne();
        $second = WorkOrderStatus::factory()->recycle($team)->createOne([
            'name' => 'Second',
        ]);
        $first = WorkOrderStatus::factory()->recycle($team)->createOne([
            'name' => 'First',
        ]);

        $first->moveOrderUp();

        login(team: $team);

        $response = get(route('teams.work-order-statuses.index', [
            'team' => $team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($first, $team, $second): void {
                $page->component('work-order-statuses/Index')
                    ->where('baseStatuses', WorkOrderBaseStatus::options())
                    ->where('team.id', $team->sqid)
                    ->where('workOrderStatuses.0.id', $first->sqid)
                    ->where('workOrderStatuses.1.id', $second->sqid);
            });
    });
});
```

## Exact Exclusion

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\ItemGroup;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes foreign and soft deleted records', function (): void {
        $itemGroup = ItemGroup::factory()->createOne();

        ItemGroup::factory()->createOne();
        ItemGroup::factory()->trashed()->recycle($itemGroup->team)->createOne();

        login(team: $itemGroup->team);

        $response = get(route('teams.item-groups.index', [
            'team' => $itemGroup->team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($itemGroup): void {
                $page->component('item-groups/Index')
                    ->has('itemGroups', 1)
                    ->where('itemGroups.0.id', $itemGroup->sqid);
            });
    });
});
```
