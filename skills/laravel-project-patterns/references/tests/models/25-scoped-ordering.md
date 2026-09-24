# Model Tests: Scoped Sort Order

Persisted sort-order assignment restarts for each tenant or each base-state group. Interleave creations to prove separate sequences and assert the stored values.

The second example adds a base-state discriminator inside one tenant; it does not by itself prove isolation across tenants.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Models\ItemGroup;
use App\Models\Team;

it('assigns sort order independently within each tenant', function (): void {
    $firstTeam = Team::factory()->createOne();
    $secondTeam = Team::factory()->createOne();

    $first = ItemGroup::factory()->recycle($firstTeam)->createOne();
    $otherTeam = ItemGroup::factory()
        ->recycle($secondTeam)
        ->createOne();
    $second = ItemGroup::factory()->recycle($firstTeam)->createOne();

    assertDatabaseHas(ItemGroup::class, [
        'id' => $first->id,
        'sort_order' => 1,
    ]);
    assertDatabaseHas(ItemGroup::class, [
        'id' => $otherTeam->id,
        'sort_order' => 1,
    ]);
    assertDatabaseHas(ItemGroup::class, [
        'id' => $second->id,
        'sort_order' => 2,
    ]);
});
```

Composite ordering scope:

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Enums\BaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

it('assigns sort order independently within each base-state group', function (): void {
    $team = Team::factory()->createOne();

    $open = WorkOrderStatus::factory()->recycle($team)->createOne([
        'base_status' => BaseStatus::Open,
    ]);

    $blocked = WorkOrderStatus::factory()->recycle($team)->createOne([
        'base_status' => BaseStatus::Blocked,
    ]);

    $secondOpen = WorkOrderStatus::factory()->recycle($team)->createOne([
        'base_status' => BaseStatus::Open,
    ]);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $open->id,
        'sort_order' => 1,
    ]);
    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $blocked->id,
        'sort_order' => 1,
    ]);
    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $secondOpen->id,
        'sort_order' => 2,
    ]);
});
```
