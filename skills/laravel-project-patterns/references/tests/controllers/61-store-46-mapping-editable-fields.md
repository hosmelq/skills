# Store Tests: Mapping Editable Fields

POST store: Editable name, description and color mapped to typed action input; returned record selects detail route and toast.

## Stores the record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\ItemGroups\CreateItemGroup;
use App\Actions\ItemGroups\Inputs\CreateItemGroupInput;
use App\Models\ItemGroup;
use App\Models\Team;

describe('store', function (): void {
    it('stores the record', function (): void {
        $team = Team::factory()->createOne();
        $created = ItemGroup::factory()->recycle($team)->createOne([
            'name' => 'Created result',
        ]);

        login(team: $team);

        mock(CreateItemGroup::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Team $teamArgument,
                CreateItemGroupInput $input
            ): bool => $teamArgument->is($team)
                && $input->color === '#2563eb'
                && $input->description === 'Consumer electronics and accessories.'
                && $input->name === 'Electronics')
            ->andReturn($created);

        $response = post(route('teams.item-groups.store', [
            'team' => $team,
        ]), [
            'color' => '#2563eb',
            'description' => 'Consumer electronics and accessories.',
            'name' => 'Electronics',
        ]);

        $response->assertRedirectToRoute('teams.item-groups.show', [
            'team' => $team,
            'item_group' => $created,
        ])->assertToast('Item group created');
    });
});
```
