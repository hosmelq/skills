# Model Tests: Case-Insensitive Name Uniqueness

Database uniqueness for active names: same-tenant case-insensitive conflict, different-tenant success, deactivated-name reservation and reuse after soft deletion.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\ItemGroup;
use App\Models\Team;
use Illuminate\Database\UniqueConstraintViolationException;

it('enforces case-insensitive name uniqueness per tenant at the database level', function (): void {
    $itemGroup = ItemGroup::factory()->createOne([
        'name' => 'Electronics',
    ]);

    $otherTeam = Team::factory()->createOne();

    ItemGroup::factory()->recycle($otherTeam)->createOne([
        'name' => 'electronics',
    ]);

    assertDatabaseHas(ItemGroup::class, [
        'name' => 'electronics',
        'team_id' => $otherTeam->id,
    ]);

    expect(fn () => ItemGroup::factory()
        ->recycle($itemGroup->team)
        ->createOne(['name' => 'electronics']))
        ->toThrow(function (UniqueConstraintViolationException $exception): void {
            expect($exception->index)->toBe('item_groups_active_name_unique');
        });
});

it('keeps deactivated names reserved at the database level', function (): void {
    $itemGroup = ItemGroup::factory()->deactivated()->createOne([
        'name' => 'Electronics',
    ]);

    expect(fn () => ItemGroup::factory()
        ->recycle($itemGroup->team)
        ->createOne(['name' => 'ELECTRONICS']))
        ->toThrow(function (UniqueConstraintViolationException $exception): void {
            expect($exception->index)->toBe('item_groups_active_name_unique');
        });
});

it('allows reusing names after soft deletion', function (): void {
    $itemGroup = ItemGroup::factory()->trashed()->createOne([
        'name' => 'Electronics',
    ]);

    $replacement = ItemGroup::factory()
        ->recycle($itemGroup->team)
        ->createOne(['name' => 'ELECTRONICS']);

    assertDatabaseHas(ItemGroup::class, [
        'id' => $replacement->id,
        'name' => 'ELECTRONICS',
    ]);
});
```
