# Action Tests: Update Group Fields

Integration tests for group updates: persist all supported name, color and description fields, preserve omitted fields and clear nullable fields explicitly.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\ItemGroups\Inputs\UpdateItemGroupInput;
use App\Actions\ItemGroups\UpdateItemGroup;
use App\Models\ItemGroup;

it('updates a record', function (): void {
    $itemGroup = ItemGroup::factory()->createOne([
        'color' => '#2563eb',
        'description' => 'Original description.',
        'name' => 'Electronics',
    ]);

    $updatedItemGroup = resolve(UpdateItemGroup::class)->handle(
        $itemGroup,
        UpdateItemGroupInput::from([
            'color' => '#dc2626',
            'description' => 'Updated description.',
            'name' => 'Computers',
        ]),
    );

    expect($updatedItemGroup->is($itemGroup))->toBeTrue();

    assertDatabaseHas(ItemGroup::class, [
        'id' => $itemGroup->id,
        'color' => '#dc2626',
        'description' => 'Updated description.',
        'name' => 'Computers',
    ]);
});

it('updates only provided fields', function (): void {
    $itemGroup = ItemGroup::factory()->createOne([
        'color' => '#2563eb',
        'description' => 'Original description.',
        'name' => 'Electronics',
    ]);

    resolve(UpdateItemGroup::class)->handle(
        $itemGroup,
        UpdateItemGroupInput::from([
            'name' => 'Computers',
        ]),
    );

    assertDatabaseHas(ItemGroup::class, [
        'id' => $itemGroup->id,
        'color' => '#2563eb',
        'description' => 'Original description.',
        'name' => 'Computers',
    ]);
});

it('clears nullable fields', function (): void {
    $itemGroup = ItemGroup::factory()->createOne([
        'color' => '#2563eb',
        'description' => 'Original description.',
    ]);

    resolve(UpdateItemGroup::class)->handle(
        $itemGroup,
        UpdateItemGroupInput::from([
            'color' => null,
            'description' => null,
        ]),
    );

    assertDatabaseHas(ItemGroup::class, [
        'id' => $itemGroup->id,
        'color' => null,
        'description' => null,
    ]);
});
```
