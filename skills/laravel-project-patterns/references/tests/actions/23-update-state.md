# Action Tests: Update State Fields

Integration tests for state updates: base enum, visibility, name, color and description, with separate full-input, omitted-input and explicit-null cases.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderStatuses\Inputs\UpdateWorkOrderStatusInput;
use App\Actions\WorkOrderStatuses\UpdateWorkOrderStatus;
use App\Enums\BaseStatus;
use App\Models\WorkOrderStatus;

it('updates a record', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne([
        'base_status' => BaseStatus::Open,
        'color' => '#2563eb',
        'description' => 'Original description.',
        'is_member_visible' => false,
        'name' => 'Open',
    ]);

    $updatedWorkOrderStatus = resolve(UpdateWorkOrderStatus::class)->handle(
        $workOrderStatus,
        UpdateWorkOrderStatusInput::from([
            'base_status' => BaseStatus::Blocked(),
            'color' => '#dc2626',
            'description' => 'Requires review.',
            'is_member_visible' => true,
            'name' => 'Blocked',
        ]),
    );

    expect($updatedWorkOrderStatus->is($workOrderStatus))->toBeTrue();

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'base_status' => BaseStatus::Blocked,
        'color' => '#dc2626',
        'description' => 'Requires review.',
        'is_member_visible' => true,
        'name' => 'Blocked',
    ]);
});

it('updates only provided fields', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->memberVisible()->createOne([
        'description' => 'Original description.',
        'name' => 'Open',
    ]);

    $updatedWorkOrderStatus = resolve(UpdateWorkOrderStatus::class)->handle(
        $workOrderStatus,
        UpdateWorkOrderStatusInput::from([
            'is_member_visible' => false,
            'name' => 'Intake review',
        ]),
    );

    expect($updatedWorkOrderStatus->is($workOrderStatus))->toBeTrue();

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'description' => 'Original description.',
        'is_member_visible' => false,
        'name' => 'Intake review',
    ]);
});

it('clears nullable fields', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne([
        'color' => '#2563eb',
        'description' => 'Original description.',
    ]);

    resolve(UpdateWorkOrderStatus::class)->handle(
        $workOrderStatus,
        UpdateWorkOrderStatusInput::from([
            'color' => null,
            'description' => null,
        ]),
    );

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'color' => null,
        'description' => null,
    ]);
});
```
