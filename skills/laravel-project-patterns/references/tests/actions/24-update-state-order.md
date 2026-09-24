# Action Tests: Move Between State Groups

Integration tests for changing a state base group: append to a populated group, start at one in an empty group and preserve order when the base is omitted. Assert old and new scoped ordering.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\WorkOrderStatuses\Inputs\UpdateWorkOrderStatusInput;
use App\Actions\WorkOrderStatuses\UpdateWorkOrderStatus;
use App\Enums\BaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

it('moves a status to the end of a new base status group', function (): void {
    $team = Team::factory()->createOne();

    $movingWorkOrderStatus = WorkOrderStatus::factory()
        ->recycle($team)
        ->createOne();

    $remainingOpenWorkOrderStatus = WorkOrderStatus::factory()
        ->recycle($team)
        ->createOne();

    $blockedWorkOrderStatuses = WorkOrderStatus::factory()
        ->count(2)
        ->recycle($team)
        ->create([
            'base_status' => BaseStatus::Blocked,
        ]);

    resolve(UpdateWorkOrderStatus::class)->handle(
        $movingWorkOrderStatus,
        UpdateWorkOrderStatusInput::from([
            'base_status' => BaseStatus::Blocked(),
        ]),
    );

    $blockedWorkOrderStatusIds = $team->workOrderStatuses()
        ->where('base_status', BaseStatus::Blocked)
        ->ordered()
        ->pluck('id')
        ->all();

    $openWorkOrderStatusIds = $team->workOrderStatuses()
        ->where('base_status', BaseStatus::Open)
        ->ordered()
        ->pluck('id')
        ->all();

    $expectedBlockedWorkOrderStatusIds = [
        ...$blockedWorkOrderStatuses->pluck('id')->all(),
        $movingWorkOrderStatus->id,
    ];

    expect($blockedWorkOrderStatusIds)->toBe($expectedBlockedWorkOrderStatusIds)
        ->and($openWorkOrderStatusIds)->toBe([$remainingOpenWorkOrderStatus->id]);
});

it('sets sort order to one when moving into an empty base status group', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne([
        'base_status' => BaseStatus::Open,
        'sort_order' => 5,
    ]);

    resolve(UpdateWorkOrderStatus::class)->handle(
        $workOrderStatus,
        UpdateWorkOrderStatusInput::from([
            'base_status' => BaseStatus::Blocked(),
        ]),
    );

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'base_status' => BaseStatus::Blocked,
        'sort_order' => 1,
    ]);
});

it('leaves status order unchanged when base status is omitted', function (): void {
    $team = Team::factory()->createOne();

    [$firstWorkOrderStatus, $workOrderStatus, $thirdWorkOrderStatus] = WorkOrderStatus::factory()
        ->count(3)
        ->recycle($team)
        ->create();

    resolve(UpdateWorkOrderStatus::class)->handle(
        $workOrderStatus,
        UpdateWorkOrderStatusInput::from([
            'name' => 'Intake review',
        ]),
    );

    $workOrderStatusIds = $team->workOrderStatuses()
        ->where('base_status', BaseStatus::Open)
        ->ordered()
        ->pluck('id')
        ->all();

    assertDatabaseHas(WorkOrderStatus::class, [
        'id' => $workOrderStatus->id,
        'base_status' => BaseStatus::Open,
        'name' => 'Intake review',
    ]);

    expect($workOrderStatusIds)->toBe([
        $firstWorkOrderStatus->id,
        $workOrderStatus->id,
        $thirdWorkOrderStatus->id,
    ]);
});
```
