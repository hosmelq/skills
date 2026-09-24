# Action Tests: Move Within a Tenant

Integration tests for moving a record within tenant order: after a predecessor, to the head with no predecessor and while inactive. Assert the complete ordered ID list.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ItemGroups\MoveItemGroup;
use App\Models\ItemGroup;
use App\Models\Team;

it('moves a group after another group in its tenant', function (): void {
    $team = Team::factory()->createOne();

    [$first, $second, $third] = ItemGroup::factory()
        ->count(3)
        ->recycle($team)
        ->create();

    $otherTeamItemGroup = ItemGroup::factory()->createOne();

    resolve(MoveItemGroup::class)->handle($first, $second);

    $itemGroupIds = $team->itemGroups()
        ->ordered()
        ->pluck('id')
        ->all();

    expect($itemGroupIds)->toBe([
        $second->id,
        $first->id,
        $third->id,
    ]);

    assertDatabaseHas(ItemGroup::class, [
        'id' => $otherTeamItemGroup->id,
        'sort_order' => 1,
    ]);
});

it('moves a group to the start when no predecessor is supplied', function (): void {
    $team = Team::factory()->createOne();

    [$first, $second] = ItemGroup::factory()
        ->count(2)
        ->recycle($team)
        ->create();

    resolve(MoveItemGroup::class)->handle($second, null);

    $itemGroupIds = $team->itemGroups()
        ->ordered()
        ->pluck('id')
        ->all();

    expect($itemGroupIds)->toBe([
        $second->id,
        $first->id,
    ]);
});

it('moves a deactivated group within its tenant', function (): void {
    $team = Team::factory()->createOne();

    $active = ItemGroup::factory()->recycle($team)->createOne();
    $deactivated = ItemGroup::factory()
        ->deactivated()
        ->recycle($team)
        ->createOne();

    resolve(MoveItemGroup::class)->handle($deactivated, null);

    $itemGroupIds = $team->itemGroups()
        ->ordered()
        ->pluck('id')
        ->all();

    expect($itemGroupIds)->toBe([
        $deactivated->id,
        $active->id,
    ]);
});
```
