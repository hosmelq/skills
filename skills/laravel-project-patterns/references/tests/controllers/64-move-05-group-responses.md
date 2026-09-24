# Move Tests: Group Ordering Responses

Pest browser PATCH reorder within a base-status group: move after a sibling, move an active record to the start, and move an inactive record to the start. Preserve typed action arguments and null predecessors.

Order: sibling placement, active start, inactive start. Factories share the same received base-status group. Mocked actions verify delegation plus redirect/toast, not persisted ordering.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrderStatuses\MoveWorkOrderStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

it('moves the record after another record in the same group', function (): void {
    $team = Team::factory()->createOne();

    $firstReceived = WorkOrderStatus::factory()->recycle($team)->createOne();
    $secondReceived = WorkOrderStatus::factory()->recycle($team)->createOne();

    signIn(team: $team);

    mock(MoveWorkOrderStatus::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (
            WorkOrderStatus $workOrderStatusArgument,
            WorkOrderStatus $afterWorkOrderStatusArgument
        ): bool => $workOrderStatusArgument->is($firstReceived)
            && $afterWorkOrderStatusArgument->is($secondReceived));

    $response = patch(route('teams.work-order-statuses.move', [
        'team' => $team,
        'work_order_status' => $firstReceived,
    ]), [
        'move_after_id' => $secondReceived->public_id,
    ]);

    $response->assertRedirect()
        ->assertToast('Work order status moved');
});

it('moves the record to the start of its group', function (): void {
    $team = Team::factory()->createOne();

    $workOrderStatus = WorkOrderStatus::factory()->recycle($team)->createOne();

    signIn(team: $team);

    mock(MoveWorkOrderStatus::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (
            WorkOrderStatus $workOrderStatusArgument,
            null $afterWorkOrderStatusArgument
        ): bool => $workOrderStatusArgument->is($workOrderStatus)
            && $afterWorkOrderStatusArgument === null);

    $response = patch(route('teams.work-order-statuses.move', [
        'team' => $team,
        'work_order_status' => $workOrderStatus,
    ]), [
        'move_after_id' => null,
    ]);

    $response->assertRedirect()
        ->assertToast('Work order status moved');
});

it('moves an inactive record to the start of its group', function (): void {
    $team = Team::factory()->createOne();

    $workOrderStatus = WorkOrderStatus::factory()
        ->deactivated()
        ->recycle($team)
        ->createOne();

    signIn(team: $team);

    mock(MoveWorkOrderStatus::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (
            WorkOrderStatus $workOrderStatusArgument,
            null $afterWorkOrderStatusArgument
        ): bool => $workOrderStatusArgument->is($workOrderStatus)
            && $afterWorkOrderStatusArgument === null);

    $response = patch(route('teams.work-order-statuses.move', [
        'team' => $team,
        'work_order_status' => $workOrderStatus,
    ]), [
        'move_after_id' => null,
    ]);

    $response->assertRedirect()
        ->assertToast('Work order status moved');
});
```
