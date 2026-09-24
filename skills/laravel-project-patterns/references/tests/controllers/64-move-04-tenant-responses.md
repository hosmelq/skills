# Move Tests: Tenant-Wide Ordering Responses

Pest browser PATCH reorder within a tenant: move after a sibling or move an inactive record to the start with an explicit null predecessor. Typed mocked-action arguments preserve model identity, followed by redirect and toast.

Order: placement after a sibling, then inactive-record placement at the start. Mocked actions verify delegation, not persisted ordering.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ItemGroups\MoveItemGroup;
use App\Models\ItemGroup;
use App\Models\Team;

it('moves the record after another record in the same tenant', function (): void {
    $team = Team::factory()->createOne();
    $first = ItemGroup::factory()->recycle($team)->createOne();
    $second = ItemGroup::factory()->recycle($team)->createOne();

    login(team: $team);

    mock(MoveItemGroup::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (
            ItemGroup $itemGroupArgument,
            ItemGroup $afterItemGroupArgument
        ): bool => $itemGroupArgument->is($first)
            && $afterItemGroupArgument->is($second));

    $response = patch(route('teams.item-groups.move', [
        'team' => $team,
        'item_group' => $first,
    ]), [
        'move_after_id' => $second->sqid,
    ]);

    $response->assertRedirect()
        ->assertToast('Item group moved');
});

it('moves an inactive record to the start', function (): void {
    $itemGroup = ItemGroup::factory()->deactivated()->createOne();

    login(team: $itemGroup->team);

    mock(MoveItemGroup::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (
            ItemGroup $itemGroupArgument,
            null $afterItemGroupArgument
        ): bool => $itemGroupArgument->is($itemGroup)
            && $afterItemGroupArgument === null);

    $response = patch(route('teams.item-groups.move', [
        'team' => $itemGroup->team,
        'item_group' => $itemGroup,
    ]), [
        'move_after_id' => null,
    ]);

    $response->assertRedirect()
        ->assertToast('Item group moved');
});
```
