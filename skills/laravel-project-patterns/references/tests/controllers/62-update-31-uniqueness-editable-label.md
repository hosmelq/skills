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
    it('rejects a value reserved by an inactive record', function (): void {
        $workOrderItemGroup = ItemGroup::factory()->createOne();
        $duplicate = ItemGroup::factory()
            ->deactivated()
            ->recycle($workOrderItemGroup->team)
            ->createOne(['name' => 'Electronics']);

        signIn(team: $workOrderItemGroup->team);

        $response = patch(route('teams.item-groups.update', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
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
        $workOrderItemGroup = ItemGroup::factory()->createOne();

        signIn(team: $workOrderItemGroup->team);

        mock(UpdateItemGroup::class)
            ->shouldReceive('handle')
            ->once();

        $response = patch(route('teams.item-groups.update', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
        ]), [
            'name' => 'electronics',
        ]);

        $response->assertRedirectToRoute('teams.item-groups.show', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
        ]);
    });

    it('allows a value used by a soft deleted record', function (): void {
        $workOrderItemGroup = ItemGroup::factory()->createOne();

        ItemGroup::factory()
            ->trashed()
            ->recycle($workOrderItemGroup->team)
            ->createOne(['name' => 'Electronics']);

        signIn(team: $workOrderItemGroup->team);

        mock(UpdateItemGroup::class)
            ->shouldReceive('handle')
            ->once();

        $response = patch(route('teams.item-groups.update', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
        ]), [
            'name' => 'ELECTRONICS',
        ]);

        $response->assertRedirectToRoute('teams.item-groups.show', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
        ]);
    });

    it('maps a partial update with null and the current name', function (): void {
        $workOrderItemGroup = ItemGroup::factory()->createOne([
            'name' => 'Electronics',
        ]);

        signIn(team: $workOrderItemGroup->team);

        mock(UpdateItemGroup::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                ItemGroup $workOrderItemGroupArgument,
                UpdateItemGroupInput $input
            ): bool => $workOrderItemGroupArgument->is($workOrderItemGroup)
                && $input->description === null
                && $input->name === 'ELECTRONICS');

        $response = patch(route('teams.item-groups.update', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
        ]), [
            'description' => null,
            'name' => 'ELECTRONICS',
        ]);

        $response->assertRedirectToRoute('teams.item-groups.show', [
            'team' => $workOrderItemGroup->team,
            'item_group' => $workOrderItemGroup,
        ])->assertToast('Item group updated');
    });
});
```
