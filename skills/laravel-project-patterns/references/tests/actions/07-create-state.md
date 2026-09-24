# Action Tests: Create a State

Integration tests for creating an ordered state: base enum, visibility flag, color, description, tenant ownership and required-only defaults.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderStatuses\CreateWorkOrderStatus;
use App\Actions\WorkOrderStatuses\Inputs\CreateWorkOrderStatusInput;
use App\Enums\BaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

it('creates a record', function (): void {
    $team = Team::factory()->createOne();

    $workOrderStatus = resolve(CreateWorkOrderStatus::class)->handle(
        $team,
        CreateWorkOrderStatusInput::from([
            'base_status' => BaseStatus::Blocked(),
            'color' => '#2563eb',
            'description' => 'Needs manual review.',
            'is_member_visible' => true,
            'name' => 'Manual review',
        ]),
    );

    expect($workOrderStatus)->toBeInstanceOf(WorkOrderStatus::class);

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'team_id' => $team->id,
        'base_status' => BaseStatus::Blocked,
        'color' => '#2563eb',
        'description' => 'Needs manual review.',
        'is_initial' => false,
        'is_member_visible' => true,
        'name' => 'Manual review',
        'sort_order' => 1,
    ]);
});

it('creates a record with only required fields', function (): void {
    $team = Team::factory()->createOne();

    $workOrderStatus = resolve(CreateWorkOrderStatus::class)->handle(
        $team,
        CreateWorkOrderStatusInput::from([
            'base_status' => BaseStatus::Open(),
            'name' => 'Intake review',
        ]),
    );

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'team_id' => $team->id,
        'base_status' => BaseStatus::Open,
        'color' => null,
        'description' => null,
        'is_initial' => false,
        'is_member_visible' => false,
        'name' => 'Intake review',
        'sort_order' => 1,
    ]);
});
```
