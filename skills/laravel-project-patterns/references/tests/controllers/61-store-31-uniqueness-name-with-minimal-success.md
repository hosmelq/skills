# Store Tests: Uniqueness Name With Minimal Success

Pest POST store: Case-insensitive inactive-name reservation, reuse across tenant and after deletion; reuse only asserts action once and detail redirect.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ItemGroups\CreateItemGroup;
use App\Models\ItemGroup;
use App\Models\Team;

describe('store', function (): void {
    it('rejects a case-insensitive value reserved by an inactive record', function (): void {
        $itemGroup = ItemGroup::factory()->deactivated()->createOne([
            'name' => 'Electronics',
        ]);

        login(team: $itemGroup->team);

        $response = post(route('teams.item-groups.store', [
            'team' => $itemGroup->team,
        ]), [
            'name' => 'ELECTRONICS',
        ]);

        $response->assertRedirectBackWithErrors([
            'name' => 'The name has already been taken.',
        ]);
    });

    it('allows a value used in a different scope', function (): void {
        ItemGroup::factory()->createOne([
            'name' => 'Electronics',
        ]);
        $team = Team::factory()->createOne();
        $created = ItemGroup::factory()->recycle($team)->createOne([
            'name' => 'Created result',
        ]);

        login(team: $team);

        mock(CreateItemGroup::class)
            ->shouldReceive('handle')
            ->once()
            ->andReturn($created);

        $response = post(route('teams.item-groups.store', [
            'team' => $team,
        ]), [
            'name' => 'electronics',
        ]);

        $response->assertRedirectToRoute('teams.item-groups.show', [
            'team' => $team,
            'item_group' => $created,
        ]);
    });

    it('allows a value used by a soft deleted record', function (): void {
        $deleted = ItemGroup::factory()->trashed()->createOne([
            'name' => 'Electronics',
        ]);
        $created = ItemGroup::factory()
            ->recycle($deleted->team)
            ->createOne(['name' => 'Created result']);

        login(team: $deleted->team);

        mock(CreateItemGroup::class)
            ->shouldReceive('handle')
            ->once()
            ->andReturn($created);

        $response = post(route('teams.item-groups.store', [
            'team' => $deleted->team,
        ]), [
            'name' => 'ELECTRONICS',
        ]);

        $response->assertRedirectToRoute('teams.item-groups.show', [
            'team' => $deleted->team,
            'item_group' => $created,
        ]);
    });
});
```
