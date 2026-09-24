# Update Tests: Uniqueness Editable Label

Pest PATCH update: Case-insensitive inactive-name reservation, another tenant, soft-deleted reuse and current-name acceptance with an explicit null description.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ItemGroups\Inputs\UpdateItemGroupInput;
use App\Actions\ItemGroups\UpdateItemGroup;
use App\Models\ItemGroup;

describe('update', function (): void {
    it('rejects a case-insensitive value reserved by an inactive record', function (): void {
        $itemGroup = ItemGroup::factory()->createOne();
        $duplicate = ItemGroup::factory()
            ->deactivated()
            ->recycle($itemGroup->team)
            ->createOne(['name' => 'Electronics']);

        login(team: $itemGroup->team);

        $response = patch(route('teams.item-groups.update', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]), [
            'name' => mb_strtolower($duplicate->name),
        ]);

        $response->assertRedirectBackWithErrors([
            'name' => 'The name has already been taken.',
        ]);
    });

    it('allows a value used in a different scope', function (): void {
        ItemGroup::factory()->createOne([
            'name' => 'Electronics',
        ]);
        $itemGroup = ItemGroup::factory()->createOne();

        login(team: $itemGroup->team);

        mock(UpdateItemGroup::class)
            ->shouldReceive('handle')
            ->once();

        $response = patch(route('teams.item-groups.update', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]), [
            'name' => 'electronics',
        ]);

        $response->assertRedirectToRoute('teams.item-groups.show', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]);
    });

    it('allows a value used by a soft deleted record', function (): void {
        $itemGroup = ItemGroup::factory()->createOne();

        ItemGroup::factory()
            ->trashed()
            ->recycle($itemGroup->team)
            ->createOne(['name' => 'Electronics']);

        login(team: $itemGroup->team);

        mock(UpdateItemGroup::class)
            ->shouldReceive('handle')
            ->once();

        $response = patch(route('teams.item-groups.update', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]), [
            'name' => 'ELECTRONICS',
        ]);

        $response->assertRedirectToRoute('teams.item-groups.show', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]);
    });

    it('maps a partial update with null and the current name', function (): void {
        $itemGroup = ItemGroup::factory()->createOne([
            'name' => 'Electronics',
        ]);

        login(team: $itemGroup->team);

        mock(UpdateItemGroup::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                ItemGroup $itemGroupArgument,
                UpdateItemGroupInput $input
            ): bool => $itemGroupArgument->is($itemGroup)
                && $input->description === null
                && $input->name === 'ELECTRONICS');

        $response = patch(route('teams.item-groups.update', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]), [
            'description' => null,
            'name' => 'ELECTRONICS',
        ]);

        $response->assertRedirectToRoute('teams.item-groups.show', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ])->assertToast('Item group updated');
    });
});
```
