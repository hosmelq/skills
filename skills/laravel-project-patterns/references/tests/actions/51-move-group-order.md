# Action Tests: Move Within a State Group

Integration tests for moving a state within its tenant and base group: after a predecessor, to the head with no predecessor and while inactive. Assert the complete scoped ordered ID list.

```php
<?php

declare(strict_types=1);

use App\Actions\WorkOrderStatuses\MoveWorkOrderStatus;
use App\Enums\BaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

it('moves a status after another status', function (): void {
    $team = Team::factory()->createOne();

    [$firstWorkOrderStatus, $secondWorkOrderStatus, $thirdWorkOrderStatus] = WorkOrderStatus::factory()
        ->count(3)
        ->recycle($team)
        ->create();

    $otherGroupWorkOrderStatus = WorkOrderStatus::factory()->recycle($team)->createOne([
        'base_status' => BaseStatus::Blocked,
    ]);

    resolve(MoveWorkOrderStatus::class)->handle($firstWorkOrderStatus, $secondWorkOrderStatus);

    $openWorkOrderStatusIds = $team->workOrderStatuses()
        ->where('base_status', BaseStatus::Open)
        ->ordered()
        ->pluck('id')
        ->all();

    $blockedWorkOrderStatusIds = $team->workOrderStatuses()
        ->where('base_status', BaseStatus::Blocked)
        ->ordered()
        ->pluck('id')
        ->all();

    expect($openWorkOrderStatusIds)->toBe([
        $secondWorkOrderStatus->id,
        $firstWorkOrderStatus->id,
        $thirdWorkOrderStatus->id,
    ])->and($blockedWorkOrderStatusIds)->toBe([$otherGroupWorkOrderStatus->id]);
});

it('moves a status to the start when no predecessor is supplied', function (): void {
    $team = Team::factory()->createOne();

    [$firstWorkOrderStatus, $secondWorkOrderStatus] = WorkOrderStatus::factory()
        ->count(2)
        ->recycle($team)
        ->create();

    resolve(MoveWorkOrderStatus::class)->handle($secondWorkOrderStatus, null);

    $workOrderStatusIds = $team->workOrderStatuses()
        ->where('base_status', BaseStatus::Open)
        ->ordered()
        ->pluck('id')
        ->all();

    expect($workOrderStatusIds)->toBe([
        $secondWorkOrderStatus->id,
        $firstWorkOrderStatus->id,
    ]);
});

it('moves a deactivated status within its group', function (): void {
    $team = Team::factory()->createOne();

    $activeWorkOrderStatus = WorkOrderStatus::factory()
        ->recycle($team)
        ->createOne();

    $deactivatedWorkOrderStatus = WorkOrderStatus::factory()
        ->deactivated()
        ->recycle($team)
        ->createOne();

    resolve(MoveWorkOrderStatus::class)->handle($deactivatedWorkOrderStatus, null);

    $workOrderStatusIds = $team->workOrderStatuses()
        ->where('base_status', BaseStatus::Open)
        ->ordered()
        ->pluck('id')
        ->all();

    expect($workOrderStatusIds)->toBe([
        $deactivatedWorkOrderStatus->id,
        $activeWorkOrderStatus->id,
    ]);
});
```
