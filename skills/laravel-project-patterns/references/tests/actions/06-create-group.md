# Action Tests: Create a Sortable Group

Integration tests for creating an ordered group: supported name, color and description fields, tenant ownership and nullable defaults with required-only input.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ItemGroups\CreateItemGroup;
use App\Actions\ItemGroups\Inputs\CreateItemGroupInput;
use App\Models\ItemGroup;
use App\Models\Team;

it('creates a record', function (): void {
    $team = Team::factory()->createOne();

    $itemGroup = resolve(CreateItemGroup::class)->handle(
        $team,
        CreateItemGroupInput::from([
            'color' => '#2563eb',
            'description' => 'Consumer electronics and accessories.',
            'name' => 'Electronics',
        ]),
    );

    expect($itemGroup)->toBeInstanceOf(ItemGroup::class);

    assertDatabaseHas(ItemGroup::class, [
        'id' => $itemGroup->id,
        'team_id' => $team->id,
        'color' => '#2563eb',
        'deactivated_at' => null,
        'description' => 'Consumer electronics and accessories.',
        'name' => 'Electronics',
        'sort_order' => 1,
    ]);
});

it('creates a record with only required fields', function (): void {
    $team = Team::factory()->createOne();

    $itemGroup = resolve(CreateItemGroup::class)->handle(
        $team,
        CreateItemGroupInput::from([
            'name' => 'Electronics',
        ]),
    );

    assertDatabaseHas(ItemGroup::class, [
        'id' => $itemGroup->id,
        'team_id' => $team->id,
        'color' => null,
        'description' => null,
        'name' => 'Electronics',
        'sort_order' => 1,
    ]);
});
```
